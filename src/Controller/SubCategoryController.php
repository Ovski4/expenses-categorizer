<?php

namespace App\Controller;

use App\Entity\SubCategory;
use App\Form\SubCategoryType;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/sub/category')]
class SubCategoryController extends AbstractController
{
    #[Route('/new', name: 'sub_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ManagerRegistry $doctrine): Response
    {
        $subCategory = new SubCategory();
        $form = $this->createForm(SubCategoryType::class, $subCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $entityManager->persist($subCategory);
            $entityManager->flush();

            return $this->redirectToRoute('category_index');
        }

        return $this->render('sub_category/new.html.twig', [
            'sub_category' => $subCategory,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'sub_category_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        SubCategory $subCategory,
        Session $session,
        ManagerRegistry $doctrine
    ): Response
    {
        $form = $this->createForm(SubCategoryType::class, $subCategory);
        $form->handleRequest($request);

        if ($session->has('error')) {
            $form->addError(new FormError($session->get('error')));
            $session->remove('error');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $doctrine->getManager()->flush();

            return $this->redirectToRoute('category_index');
        }

        return $this->render('sub_category/edit.html.twig', [
            'sub_category' => $subCategory,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'sub_category_delete', methods: ['DELETE'])]
    public function delete(
        Request $request,
        Session $session,
        SubCategory $subCategory,
        TranslatorInterface $translator,
        ManagerRegistry $doctrine
    ): Response
    {
        if ($this->isCsrfTokenValid('delete'.$subCategory->getId(), $request->request->get('_token'))) {
            $entityManager = $doctrine->getManager();
            $entityManager->remove($subCategory);

            try {
                $entityManager->flush();
            } catch(ForeignKeyConstraintViolationException $e) {
                $session->set(
                    'error',
                    $translator->trans('This sub category cannot be deleted while transactions or rules are associated with it.')
                );

                return $this->redirect($request->headers->get('referer'));
            }

        }

        return $this->redirectToRoute('category_index');
    }
}
