<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Form\StatementType;
use App\Form\TransactionType;
use App\Services\CcmParserClient;
use App\Services\StatementUploader;
use App\Services\TransactionCategorizer;
use App\Services\TransactionExporter;
use Doctrine\ORM\EntityManagerInterface;
use Elasticsearch\Common\Exceptions\NoNodesAvailableException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

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
    public function categorize(Request $request, TransactionCategorizer $transactionCategorizer): Response
    {
        if ($request->isMethod('PATCH')) {
            $transactions = $transactionCategorizer->categorizeAllSync();

            return $this->render('transaction/categorize.html.twig', [
                'transactions' => $transactions,
            ]);
        }

        return $this->render('transaction/categorize.html.twig');
    }

    /**
     * @Route("/export", name="transaction_export", methods={"PATCH", "GET"})
     */
    public function export(Request $request, TransactionExporter $transactionExporter): Response
    {
        if ($request->isMethod('PATCH')) {
            try {
                $exportData = $transactionExporter->exportAllSync();
            } catch(NoNodesAvailableException $e) {
                return $this->render('transaction/export.html.twig', [
                    'error' => 'Elasticsearch seems to be down'
                ]);
            }

            return $this->render('transaction/export.html.twig', $exportData);
        }

        return $this->render('transaction/export.html.twig');
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
            $statementFile = $statementUploader->upload($statementFile);

            return $this->redirect(
                $this->generateUrl('validate_transactions', ['statement' => $statementFile])
            );
        }

        return $this->render('transaction/upload_statement.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/import/validate-transactions/{statement}", name="validate_transactions", methods={"GET", "POST"})
     */
    public function validateTransactions(
        string $statement,
        Request $request,
        CcmParserClient $ccmParserClient,
        EntityManagerInterface $manager,
        ParameterBagInterface $params
    ): Response
    {
        $transactions = $ccmParserClient->parse($params->get('app.statements_dir') . $statement);

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

        return $this->render('transaction/validate_transactions.html.twig', [
            'transactions' => $transactions,
            'existingTransactionCount' => $existingTransactionCount,
            'statement' => $statement
        ]);
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
     * @Route("/{id}", name="transaction_show", methods={"GET"})
     */
    public function show(Transaction $transaction): Response
    {
        return $this->render('transaction/show.html.twig', [
            'transaction' => $transaction,
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
