<?php

namespace App\Controller;

use App\Event\TransactionCategorizedEvent;
use App\Event\TransactionCategoryChangedEvent;
use App\Event\TransactionMatchesMultipleRulesEvent;
use App\Services\TransactionCategorizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[Route('/transaction')]
class TransactionCategorizeController extends AbstractController
{
    #[Route('/categorize', name: 'transaction_categorize', methods: ['PATCH', 'GET'])]
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
                TransactionCategoryChangedEvent::NAME,
                function (TransactionCategoryChangedEvent $event) use (&$transactions) {
                    $transaction = clone $event->getTransaction();
                    $subCategory = clone $transaction->getSubCategory();
                    $subCategory->setName(sprintf(
                        '%s -> %s',
                        $event->getOldSubCategory(),
                        $transaction->getSubCategory()->getName()
                    ));
                    $transaction->setSubCategory($subCategory);

                    $transactions[] = $transaction;
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
}
