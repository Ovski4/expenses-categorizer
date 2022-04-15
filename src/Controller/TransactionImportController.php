<?php

namespace App\Controller;

use App\Entity\DecoratedTransaction;
use App\Entity\Transaction;
use App\Exception\AccountNotFoundException;
use App\Form\CsvStatementType;
use App\Form\PdfStatementType;
use App\Services\StatementUploader;
use App\Services\FileParser\FileParserRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/transaction/import')]
class TransactionImportController extends AbstractController
{
    #[Route('/', name: 'transaction_import_index')]
    public function index(FileParserRegistry $registry)
    {
        return $this->render('transaction/import/index.html.twig', [
            'parsers' => $registry->getFileParsers(),
        ]);
    }

    #[Route('/{parserName}/upload', name: 'transaction_upload_statement', methods: ['GET', 'POST'])]
    public function uploadStatement(
        Request $request,
        StatementUploader $statementUploader,
        FileParserRegistry $registry,
        string $parserName
    ) {
        $parser = $registry->getFileParser($parserName);
        $form = $parser->requiresPdfFile()
            ? $this->createForm(PdfStatementType::class)
            : $this->createForm(CsvStatementType::class)
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $statementFile = $form['statement']->getData();
            $statementFile = $statementUploader->upload($statementFile);

            $parameters = [
                'statement' => $statementFile,
                'parserName' => $parserName
            ];

            if ($parser->requiresCsvFile()) {
                $account = $form['account']->getData();
                $parameters['account'] = $account->getId();
            }

            return $this->redirect(
                $this->generateUrl('validate_transactions', $parameters)
            );
        }

        return $this->render('transaction/import/upload_statement.html.twig', [
            'form' => $form->createView(),
            'parser' => $parser,
        ]);
    }

    #[Route('/validate-transactions/{parserName}/{statement}', name: 'validate_transactions', methods: ['GET', 'POST'])]
    public function validateTransactions(
        string $parserName,
        string $statement,
        Request $request,
        ?string $account,
        ?bool $saveOnlyNewTransactions,
        FileParserRegistry $registry,
        EntityManagerInterface $manager,
        ParameterBagInterface $params,
        TranslatorInterface $translator
    ): Response
    {
        try {
            $fileParser = $registry->getFileParser($parserName);
            $transactions = $fileParser->parse(
                $params->get('app.statements_dir') . $statement,
                $account ? ['accountId' => $account] : []
            );
        } catch (AccountNotFoundException $e) {
            return $this->render('transaction/import/validate_transactions.html.twig', [
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
        } catch (ServerException $e) {
            $transactions = [];
        }

        if ($request->isMethod('POST')) {
            foreach($transactions as $transaction) {
                if (!$saveOnlyNewTransactions || !$manager->getRepository(Transaction::class)->exists($transaction)) {
                    $manager->persist($transaction);
                }
            }

            $manager->flush();

            return $this->redirectToRoute('transaction_index');
        }

        $existingTransactionCount = 0;
        foreach($transactions as $transaction) {
            $transactionExist = $manager->getRepository(Transaction::class)->exists($transaction);
            $existingTransactionCount = $transactionExist ?
                $existingTransactionCount + 1 :
                $existingTransactionCount
            ;

            $decoratedTransaction = new DecoratedTransaction($transaction);
            $decoratedTransaction->setExists($transactionExist);
            $decoratedTransactions[] = $decoratedTransaction;
        }

        if (empty($transactions)) {
            return $this->render('transaction/import/validate_transactions.html.twig', [
                'error' => sprintf(
                    '%s %s?',
                    $translator->trans('No transactions were found. Are you sure your file is a valid'),
                    strtolower($translator->trans($fileParser->getLabel()))
                ),
                'suggestionLabel' => $translator->trans('Go back to file upload'),
                'suggestionPath' => 'transaction_upload_statement',
                'suggestionPathParams' => [
                    'parserName' => $parserName
                ]
            ]);
        } else {
            return $this->render('transaction/import/validate_transactions.html.twig', [
                'transactions' => $decoratedTransactions,
                'existingTransactionCount' => $existingTransactionCount,
                'statement' => $statement,
                'parserName' => $parserName
            ]);
        }
    }
}
