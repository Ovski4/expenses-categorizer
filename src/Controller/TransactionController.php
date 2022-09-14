<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\FilterForm\TransactionFilterType;
use App\Form\TransactionType;
use App\Services\TransactionDiffChecker;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Elasticsearch\Common\Exceptions\NoNodesAvailableException;
use InvalidArgumentException;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/transaction')]
class TransactionController extends AbstractController
{
    #[Route('/', methods: ['GET'], name: 'transaction_index')]
    public function index(
        Request $request,
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,
        FilterBuilderUpdaterInterface $filterBuilderUpdater
    ): Response
    {
        $hasFilters = false;
        $filterForm = $formFactory->create(TransactionFilterType::class);

        $queryBuilder = $entityManager->createQueryBuilder()
            ->select('transaction')
            ->from(Transaction::class, 'transaction')
            ->orderBy('transaction.createdAt', 'desc')
        ;

        if ($request->query->has($filterForm->getName())) {
            $hasFilters = true;
            $filterForm->submit($request->query->all($filterForm->getName()));

            try {
                $filterBuilderUpdater->addFilterConditions($filterForm, $queryBuilder);
            } catch (InvalidArgumentException $e) {
                // form validation will do the rest
            }
        }

        $adapter = new QueryAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(20);

        if ($request->query->has('page')) {
            $pagerfanta->setCurrentPage($request->get('page'));
        }

        return $this->render('transaction/index.html.twig', [
            'pager' => $pagerfanta,
            'filter_form' => $filterForm->createView(),
            'has_filters' => $hasFilters,
        ]);
    }

    #[Route('/new', name: 'transaction_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ManagerRegistry $doctrine): Response
    {
        $transaction = new Transaction();
        $form = $this->createForm(TransactionType::class, $transaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if($transaction->isCategorized()) {
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
        TransactionDiffChecker $transactionDiffChecker
    ): Response
    {
        $form = $this->createForm(TransactionType::class, $transaction);
        $form->handleRequest($request);

        if ($session->has('error')) {
            $form->addError(new FormError($session->get('error')));
            $session->remove('error');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            if($transactionDiffChecker->subCategoryChanged($transaction)) {
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

    #[Route('/{id}', name: 'transaction_delete', methods: ['DELETE'])]
    public function delete(
        Request $request,
        Transaction $transaction,
        TranslatorInterface $translator,
        Session $session,
        ManagerRegistry $doctrine
    ) : Response
    {
        if ($this->isCsrfTokenValid('delete'.$transaction->getId(), $request->request->get('_token'))) {
            $entityManager = $doctrine->getManager();
            try {
                $entityManager->remove($transaction);
            } catch(NoNodesAvailableException $e) {
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
