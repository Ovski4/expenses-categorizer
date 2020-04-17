<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Form\TransactionType;
use App\Services\RuleChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

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
    public function categorize(Request $request, EntityManagerInterface $entityManager, RuleChecker $ruleChecker): Response
    {
        if ($request->isMethod('PATCH')) {
            $uncategorizedTransactions = $entityManager
                ->getRepository(Transaction::class)
                ->findUncategorizedTransactions()
            ;

            $transactions = [];
            foreach ($uncategorizedTransactions as $transaction) {
                $subCategory = $ruleChecker->getMatchingSubCategory($transaction);
                if ($subCategory) {
                    $transaction->setSubCategory($subCategory);
                    $entityManager->persist($transaction);
                    $transactions[] = $transaction;
                }
            }

            $entityManager->flush();

            return $this->render('transaction/categorize.html.twig', [
                'transactions' => $transactions,
            ]);
        }

        return $this->render('transaction/categorize.html.twig');
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
