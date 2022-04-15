<?php

namespace App\Controller;

use App\Entity\DecoratedTransaction;
use App\Entity\Settings;
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
    #[Route('/upload-statement', name: 'transaction_upload_statement', methods: ['GET', 'POST'])]
    public function uploadStatement(
        Request $request,
        StatementUploader $statementUploader,
        EntityManagerInterface $manager
    ): Response
    {
        $pdfStatementForm = $this->createForm(PdfStatementType::class);
        $pdfStatementForm->handleRequest($request);

        if ($pdfStatementForm->isSubmitted() && $pdfStatementForm->isValid()) {
            $statementFile = $pdfStatementForm['statement']->getData();
            $parserName = $pdfStatementForm['parserName']->getData();
            $statementFile = $statementUploader->upload($statementFile);

            $manager->getRepository(Settings::class)->createOrUpdate(Settings::NAME_LAST_PDF_STATEMENT_PARSER_USED, $parserName);

            return $this->redirect(
                $this->generateUrl('validate_transactions', [
                    'statement' => $statementFile,
                    'parserName' => $parserName
                ])
            );
        }

        $csvStatementForm = $this->createForm(CsvStatementType::class);
        $accountOptions = $csvStatementForm
            ->get('account')
            ->getConfig()
            ->getOption('query_builder')
            ->getQuery()
            ->getResult()
        ;

        $csvStatementForm->handleRequest($request);

        if ($csvStatementForm->isSubmitted() && $csvStatementForm->isValid()) {
            $statementFile = $csvStatementForm['statement']->getData();
            $parserName = $csvStatementForm['parserName']->getData();
            $account = $csvStatementForm['account']->getData();
            $statementFile = $statementUploader->upload($statementFile);

            $manager->getRepository(Settings::class)->createOrUpdate(Settings::NAME_LAST_CSV_PARSER_USED, $parserName);

            return $this->redirect(
                $this->generateUrl('validate_transactions', [
                    'statement' => $statementFile,
                    'parserName' => $parserName,
                    'account' => $account->getId()
                ])
            );
        }

        return $this->render('transaction/upload_statement.html.twig', [
            'pdf_statement_form' => $pdfStatementForm->createView(),
            'csv_statement_form' => $csvStatementForm->createView(),
            'includes_accounts_without_aliases' => count( $accountOptions ) > 0,
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
            return $this->render('transaction/validate_transactions.html.twig', [
                'error' => sprintf(
                    '%s %s?',
                    $translator->trans('No transactions were found. Are you sure your file is a valid'),
                    strtolower($translator->trans($fileParser->getLabel()))
                ),
                'suggestionLabel' => $translator->trans('Go back to file upload'),
                'suggestionPath' => 'transaction_upload_statement'
            ]);
        } else {
            return $this->render('transaction/validate_transactions.html.twig', [
                'transactions' => $decoratedTransactions,
                'existingTransactionCount' => $existingTransactionCount,
                'statement' => $statement,
                'parserName' => $parserName
            ]);
        }
    }
}
