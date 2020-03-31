<?php

namespace App\Controller;

use App\Entity\SubCategoryTransactionRule;
use App\Entity\Transaction;
use App\Entity\TransactionType;
use App\Form\SubCategoryTransactionRuleType;
use App\Repository\SubCategoryTransactionRuleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/sub/category/transaction/rule")
 */
class SubCategoryTransactionRuleController extends AbstractController
{
    /**
     * @Route("/", name="sub_category_transaction_rule_index", methods={"GET"})
     */
    public function index(SubCategoryTransactionRuleRepository $subCategoryTransactionRuleRepository): Response
    {
        return $this->render('sub_category_transaction_rule/index.html.twig', [
            'sub_category_transaction_rules' => $subCategoryTransactionRuleRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="sub_category_transaction_rule_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $subCategoryTransactionRule = new SubCategoryTransactionRule();

        if ($request->query->has('transaction')) {
            $transaction = $this
                ->getDoctrine()
                ->getManager()
                ->getRepository(Transaction::class)
                ->findOneById($request->query->get('transaction'))
            ;
            $subCategoryTransactionRule
                ->setContains($transaction->getLabel())
                ->setTransactionType($transaction->getType())
            ;
        }

        $form = $this->createForm(SubCategoryTransactionRuleType::class, $subCategoryTransactionRule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($subCategoryTransactionRule);
            $entityManager->flush();

            return $this->redirectToRoute('sub_category_transaction_rule_index');
        }

        return $this->render('sub_category_transaction_rule/new.html.twig', [
            'sub_category_transaction_rule' => $subCategoryTransactionRule,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="sub_category_transaction_rule_show", methods={"GET"})
     */
    public function show(SubCategoryTransactionRule $subCategoryTransactionRule): Response
    {
        return $this->render('sub_category_transaction_rule/show.html.twig', [
            'sub_category_transaction_rule' => $subCategoryTransactionRule,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="sub_category_transaction_rule_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, SubCategoryTransactionRule $subCategoryTransactionRule): Response
    {
        $form = $this->createForm(SubCategoryTransactionRuleType::class, $subCategoryTransactionRule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('sub_category_transaction_rule_index');
        }

        return $this->render('sub_category_transaction_rule/edit.html.twig', [
            'sub_category_transaction_rule' => $subCategoryTransactionRule,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="sub_category_transaction_rule_delete", methods={"DELETE"})
     */
    public function delete(Request $request, SubCategoryTransactionRule $subCategoryTransactionRule): Response
    {
        if ($this->isCsrfTokenValid('delete'.$subCategoryTransactionRule->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($subCategoryTransactionRule);
            $entityManager->flush();
        }

        return $this->redirectToRoute('sub_category_transaction_rule_index');
    }
}
