<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Entity\TransactionType as TransactionTypeEnum;
use App\Exception\InvalidSubCategoryAssignmentException;
use App\FilterForm\TransactionFilterType;
use App\Form\TransactionType;
use App\Repository\SubCategoryRepository;
use App\Services\TransactionDiffChecker;
use App\Services\TransactionSubCategoryAssigner;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Elasticsearch\Common\Exceptions\NoNodesAvailableException;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Spiriit\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/transaction')]
class TransactionController extends AbstractController
{
    #[Route('/', methods: ['GET'], name: 'transaction_index')]
    public function index(
        Request $request,
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,
        FilterBuilderUpdaterInterface $filterBuilderUpdater,
        SubCategoryRepository $subCategoryRepository,
    ): Response {
        $hasFilters = false;
        $filterForm = $formFactory->create(TransactionFilterType::class);

        // account and subCategory are selected along with the transaction to avoid
        // hydrating one proxy per row when the template renders them.
        $queryBuilder = $entityManager->createQueryBuilder()
            ->select('transaction', 'account', 'subCategory')
            ->from(Transaction::class, 'transaction')
            ->leftJoin('transaction.account', 'account')
            ->leftJoin('transaction.subCategory', 'subCategory')
            ->orderBy('transaction.createdAt', 'desc')
        ;

        if ($request->query->has($filterForm->getName())) {
            $hasFilters = true;
            $filterForm->submit($request->query->all($filterForm->getName()));

            try {
                $filterBuilderUpdater->addFilterConditions($filterForm, $queryBuilder);
            } catch (\InvalidArgumentException $e) {
                // form validation will do the rest
            }
        }

        $adapter = new QueryAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(20);

        if ($request->query->has('page')) {
            $pagerfanta->setCurrentPage($request->query->getInt('page'));
        }

        // Fetched once per page, not once per uncategorized row. Fetched before the
        // filter form view is built on purpose: these queries also select the top
        // categories, so the filter form's group_by finds them in the identity map
        // instead of lazy loading one per top category.
        $subCategories = [
            TransactionTypeEnum::EXPENSES => $subCategoryRepository
                ->findByTransactionTypeGroupedByTopCategory(TransactionTypeEnum::EXPENSES),
            TransactionTypeEnum::REVENUES => $subCategoryRepository
                ->findByTransactionTypeGroupedByTopCategory(TransactionTypeEnum::REVENUES),
        ];

        return $this->render('transaction/index.html.twig', [
            'pager' => $pagerfanta,
            'filter_form' => $filterForm->createView(),
            'has_filters' => $hasFilters,
            'sub_categories' => $subCategories,
        ]);
    }

    #[Route('/new', name: 'transaction_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ManagerRegistry $doctrine): Response
    {
        $transaction = new Transaction();
        $form = $this->createForm(TransactionType::class, $transaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($transaction->isCategorized()) {
                $transaction->setCategorizedManually(true);
            }

            $entityManager = $doctrine->getManager();
            $entityManager->persist($transaction);
            $entityManager->flush();

            return $this->redirectToRoute('transaction_index');
        }

        return $this->render('transaction/new.html.twig', [
            'transaction' => $transaction,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'transaction_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Transaction $transaction,
        Session $session,
        ManagerRegistry $doctrine,
        TransactionDiffChecker $transactionDiffChecker,
    ): Response {
        $form = $this->createForm(TransactionType::class, $transaction);
        $form->handleRequest($request);

        if ($session->has('error')) {
            $form->addError(new FormError($session->get('error')));
            $session->remove('error');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            if ($transactionDiffChecker->subCategoryChanged($transaction)) {
                $transaction->setCategorizedManually($transaction->isCategorized() ? true : false);
            }

            $doctrine->getManager()->flush();

            return $this->redirectToRoute('transaction_index');
        }

        return $this->render('transaction/edit.html.twig', [
            'transaction' => $transaction,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Assigns a sub category to a transaction from the transaction list.
     *
     * Set-only: an empty or unknown sub category is rejected, never treated as a
     * request to clear the category.
     */
    #[Route('/{id}/sub-category', name: 'transaction_set_sub_category', methods: ['PATCH'])]
    public function setSubCategory(
        Request $request,
        Transaction $transaction,
        TransactionSubCategoryAssigner $assigner,
        SubCategoryRepository $subCategoryRepository,
        TranslatorInterface $translator,
    ): Response {
        $wantsJson = 'json' === $request->getPreferredFormat(null);

        if (!$this->isCsrfTokenValid('set-sub-category'.$transaction->getId(), $request->request->getString('_token'))) {
            return $this->subCategoryError(
                $request,
                $wantsJson,
                $translator->trans('Invalid security token, please reload the page'),
                Response::HTTP_FORBIDDEN
            );
        }

        $subCategoryId = $request->request->getString('subCategory');
        $subCategory = '' === $subCategoryId ? null : $subCategoryRepository->find($subCategoryId);

        if (null === $subCategory) {
            return $this->subCategoryError(
                $request,
                $wantsJson,
                $translator->trans('A sub category is required'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        try {
            $assigner->assign($transaction, $subCategory);
        } catch (InvalidSubCategoryAssignmentException $e) {
            return $this->subCategoryError(
                $request,
                $wantsJson,
                $translator->trans($e->getMessage(), [], 'validators'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        if ($wantsJson) {
            return new JsonResponse([
                'id' => $transaction->getId(),
                'subCategory' => [
                    'id' => $subCategory->getId(),
                    'name' => $subCategory->getName(),
                ],
            ]);
        }

        return $this->redirectToTransactionList($request);
    }

    private function subCategoryError(Request $request, bool $wantsJson, string $message, int $status): Response
    {
        if ($wantsJson) {
            return new JsonResponse(['error' => $message], $status);
        }

        $this->addFlash('error', $message);

        return $this->redirectToTransactionList($request);
    }

    /**
     * Sends the no-javascript fallback back to the list it came from, keeping the
     * active filters and page number.
     */
    private function redirectToTransactionList(Request $request): Response
    {
        $queryString = parse_url((string) $request->headers->get('referer'), PHP_URL_QUERY);

        return $this->redirect(
            $this->generateUrl('transaction_index').(is_string($queryString) && '' !== $queryString ? '?'.$queryString : '')
        );
    }

    #[Route('/{id}', name: 'transaction_delete', methods: ['DELETE'])]
    public function delete(
        Request $request,
        Transaction $transaction,
        TranslatorInterface $translator,
        Session $session,
        ManagerRegistry $doctrine,
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$transaction->getId(), $request->request->getString('_token'))) {
            $entityManager = $doctrine->getManager();
            try {
                $entityManager->remove($transaction);
            } catch (NoNodesAvailableException $e) {
                $session->set(
                    'error',
                    $translator->trans('error_deleting_transaction_in_elasticsearch')
                );

                return $this->redirect($request->headers->get('referer'));
            }

            $entityManager->flush();
        }

        return $this->redirectToRoute('transaction_index');
    }
}
