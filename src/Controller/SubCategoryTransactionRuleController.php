<?php

namespace App\Controller;

use App\Entity\SubCategoryTransactionRule;
use App\Entity\Transaction;
use App\FilterForm\SubCategoryTransactionRuleFilterType;
use App\Form\SubCategoryTransactionRuleType;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
    public function index(
        Request $request,
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,
        FilterBuilderUpdaterInterface $filterBuilderUpdater
    ): Response
    {
        $filterForm = $formFactory->create(SubCategoryTransactionRuleFilterType::class);

        $queryBuilder = $entityManager->createQueryBuilder()
            ->select('rule')
            ->from(SubCategoryTransactionRule::class, 'rule')
            ->orderBy('rule.updatedAt', 'desc')
            ->addOrderBy('rule.contains', 'asc')
        ;

        if ($request->query->has($filterForm->getName())) {
            $filterForm->submit($request->query->get($filterForm->getName()));
            $filterBuilderUpdater->addFilterConditions($filterForm, $queryBuilder);
        }

        return $this->render('sub_category_transaction_rule/index.html.twig', [
            'sub_category_transaction_rules' => $queryBuilder->getQuery()->getResult(),
            'filter_form' => $filterForm->createView(),
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
            ;
        }

        $form = $this->createForm(SubCategoryTransactionRuleType::class, $subCategoryTransactionRule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($subCategoryTransactionRule);
            $entityManager->flush();

            if (strpos($request->get('referer'), 'rule') !== false) {
                return $this->redirectToRoute('sub_category_transaction_rule_index');
            } else {
                return new RedirectResponse($request->get('referer'));
            }
        }

        return $this->render('sub_category_transaction_rule/new.html.twig', [
            'sub_category_transaction_rule' => $subCategoryTransactionRule,
            'form' => $form->createView(),
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
