<?php

namespace App\Controller;

use App\Entity\Bank;
use App\Entity\Transaction;
use App\Event\TransactionCategorizedEvent;
use App\Event\TransactionMatchesMultipleRulesEvent;
use App\Exception\AccountNotFoundException;
use App\Form\StatementType;
use App\Form\TransactionType;
use App\Services\AccountStatementParserClient;
use App\Services\Exporter\CsvExporter;
use App\Services\StatementUploader;
use App\Services\TransactionCategorizer;
use App\Services\Exporter\ElasticsearchExporter;
use Doctrine\ORM\EntityManagerInterface;
use Elasticsearch\Common\Exceptions\NoNodesAvailableException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/transaction")
 */
class TransactionController extends AbstractController
{
    /**
     * @Route("/", name="transaction_index", methods={"GET"})
     */
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $queryBuilder = $entityManager->createQueryBuilder()
            ->select('transaction')
            ->from(Transaction::class, 'transaction')
            ->orderBy('transaction.created_at', 'desc')
        ;

        if ($request->query->has('only_show_uncategorized')) {
            $queryBuilder->where('transaction.subCategory is NULL');
        }

        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(20);

        if ($request->query->has('page')) {
            $pagerfanta->setCurrentPage($request->get('page'));
        }

        return $this->render('transaction/index.html.twig', [
            'pager' => $pagerfanta,
            'only_show_uncategorized' => $request->query->has('only_show_uncategorized')
        ]);
    }

    /**
     * @Route("/categorize", name="transaction_categorize", methods={"PATCH", "GET"})
     */
    public function categorize(Request $request, TransactionCategorizer $transactionCategorizer, EventDispatcherInterface $dispatcher): Response
    {
        if ($request->isMethod('PATCH')) {
            $transactions = [];
            $errors = [];

            $dispatcher->addListener(
                TransactionCategorizedEvent::NAME,
                function (TransactionCategorizedEvent $event) use (&$transactions) {
                    $transactions[] = $event->getTransaction();
                }
            );

            $dispatcher->addListener(
                TransactionMatchesMultipleRulesEvent::NAME,
                function (TransactionMatchesMultipleRulesEvent $event) use (&$errors) {
                    $errors[] = [
                        'rules' => $event->getRules(),
                        'transaction' => $event->getTransaction()
                    ];
                }
            );

            $transactionCategorizer->categorizeAllSync();

            return $this->render('transaction/categorize.html.twig', [
                'transactions' => $transactions,
                'errors' => $errors
            ]);
        }

        return $this->render('transaction/categorize.html.twig');
    }

    /**
     * @Route("/export/elasticsearch", name="elasticsearch_export", methods={"PATCH", "GET"})
     */
    public function exportToElasticsearch(Request $request, ElasticsearchExporter $exporter, TranslatorInterface $translator): Response
    {
        if ($request->isMethod('PATCH')) {
            try {
                $exportData = $exporter->exportAllSync();
            } catch(NoNodesAvailableException $e) {
                return $this->render('transaction/export.html.twig', [
                    'error' => $translator->trans('Elasticsearch seems to be down')
                ]);
            }

            return $this->render('transaction/export.html.twig', $exportData);
        }

        return $this->render('transaction/export.html.twig');
    }

    /**
     * @Route("/export/csv", name="csv_export", methods={"GET"})
     */
    public function export(CsvExporter $csvExporter): Response
    {
        $csv = $csvExporter->export();

        $response = new Response($csv);
        $response->headers->set('Content-Type', 'application/csv');
        $response->headers->set(
            'Content-Disposition',
            sprintf(
                'attachment; filename="transactions_%s.csv"',
                (new \DateTime('now'))->format('Y-m-d')
            )
        );

        return $response;
    }

    /**
     * @Route("/import/upload-statement", name="transaction_upload_statement", methods={"GET", "POST"})
     */
    public function uploadStatement(
        Request $request,
        StatementUploader $statementUploader
    ): Response
    {
        $form = $this->createForm(StatementType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $statementFile = $form['statement']->getData();
            $parserName = $form['parserName']->getData();
            $statementFile = $statementUploader->upload($statementFile);

            return $this->redirect(
                $this->generateUrl('validate_transactions', [
                    'statement' => $statementFile,
                    'parserName' => $parserName
                ])
            );
        }

        return $this->render('transaction/upload_statement.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/import/validate-transactions/{parserName}/{statement}", name="validate_transactions", methods={"GET", "POST"})
     */
    public function validateTransactions(
        string $parserName,
        string $statement,
        Request $request,
        AccountStatementParserClient $parserClient,
        EntityManagerInterface $manager,
        ParameterBagInterface $params,
        TranslatorInterface $translator
    ): Response
    {
        try {
            $transactions = $parserClient->parse(
                $params->get('app.statements_dir') . $statement,
                $parserName
            );
        } catch (AccountNotFoundException $e) {
            return $this->render('transaction/validate_transactions.html.twig', [
                'error' => sprintf(
                    '%s. %s',
                    $translator->trans($e->getMessage(), ['%search%' => $e->getAccountSearch()]),
                    $translator->trans('You need to create an account with this name or alias before importing transactions')
                ),
                'suggestionLabel' => $translator->trans('Create an account now'),
                'suggestionPath' => 'account_new',
                'suggestionPathParams' => [
                    'search' => $e->getAccountSearch()
                ]
            ]);
        }

        if ($request->isMethod('POST')) {
            foreach($transactions as $transaction) {
                $manager->persist($transaction);
            }
            $manager->flush();

            return $this->redirectToRoute('transaction_index');
        }

        $existingTransactionCount = 0;
        foreach($transactions as $transaction) {
            $transactionExist = $manager
                ->getRepository(Transaction::class)
                ->exists($transaction)
            ;
            $existingTransactionCount = $transactionExist ?
                $existingTransactionCount + 1 :
                $existingTransactionCount
            ;
        }

        if (empty($transactions)) {
            return $this->render('transaction/validate_transactions.html.twig', [
                'error' => sprintf(
                    '%s "%s"?',
                    $translator->trans('No transactions were found. Are you sure your pdf is a valid statement file from'),
                    Bank::getByParserName($parserName)['name']
                ),
                'suggestionLabel' => $translator->trans('Go back to file upload'),
                'suggestionPath' => 'transaction_upload_statement'
            ]);
        } else {
            return $this->render('transaction/validate_transactions.html.twig', [
                'transactions' => $transactions,
                'existingTransactionCount' => $existingTransactionCount,
                'statement' => $statement,
                'parserName' => $parserName
            ]);
        }
    }

    /**
     * @Route("/new", name="transaction_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $transaction = new Transaction();
        $form = $this->createForm(TransactionType::class, $transaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($transaction);
            $entityManager->flush();

            return $this->redirectToRoute('transaction_index');
        }

        return $this->render('transaction/new.html.twig', [
            'transaction' => $transaction,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="transaction_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Transaction $transaction): Response
    {
        $form = $this->createForm(TransactionType::class, $transaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('transaction_index');
        }

        return $this->render('transaction/edit.html.twig', [
            'transaction' => $transaction,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="transaction_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Transaction $transaction): Response
    {
        if ($this->isCsrfTokenValid('delete'.$transaction->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($transaction);
            $entityManager->flush();
        }

        return $this->redirectToRoute('transaction_index');
    }
}
