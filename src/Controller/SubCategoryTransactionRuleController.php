<?php

namespace App\Controller;

use App\Entity\SubCategoryTransactionRule;
use App\Entity\Transaction;
use App\Exception\IllogicalRuleException;
use App\FilterForm\SubCategoryTransactionRuleFilterType;
use App\Form\SubCategoryTransactionRuleType;
use App\Repository\SubCategoryTransactionRuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

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
    public function new(Request $request, TranslatorInterface $translator): Response
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

        try {
            $form = $this->createForm(SubCategoryTransactionRuleType::class, $subCategoryTransactionRule);
            $form->handleRequest($request);
        } catch (IllogicalRuleException $e) {
            $form->addError(new FormError(sprintf(
                '%s "%s" %s %s',
                $translator->trans('Transaction type is set to'),
                strtolower($translator->trans($e->getTransactionType())),
                $translator->trans('but the sub category belongs to'),
                strtolower($translator->trans($e->getSubCategory()->getTransactionType()))
            )));
        } catch (\UnexpectedValueException $e) {
            $form->addError(new FormError(
                $translator->trans('The amount of the rule must be an absolute amount, and therefore be positive.')
            ));
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($subCategoryTransactionRule);
            $entityManager->flush();

            return new RedirectResponse($request->get('referer'));
        }

        return $this->render('sub_category_transaction_rule/new.html.twig', [
            'sub_category_transaction_rule' => $subCategoryTransactionRule,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="sub_category_transaction_rule_edit", methods={"GET","POST"})
     */
    public function edit(
        Request $request,
        SubCategoryTransactionRule $subCategoryTransactionRule,
        TranslatorInterface $translator
    ): Response
    {
        try {
            $form = $this->createForm(SubCategoryTransactionRuleType::class, $subCategoryTransactionRule);
            $form->handleRequest($request);
        } catch (IllogicalRuleException $e) {
            $form->addError(new FormError(sprintf(
                '%s "%s" %s %s',
                $translator->trans('Transaction type is set to'),
                strtolower($translator->trans($e->getTransactionType())),
                $translator->trans('but the sub category belongs to'),
                strtolower($translator->trans($e->getSubCategory()->getTransactionType()))
            )));
        } catch (\UnexpectedValueException $e) {
            $form->addError(new FormError(
                $translator->trans('The amount of the rule must be an absolute amount, and therefore be positive.')
            ));
        }

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
