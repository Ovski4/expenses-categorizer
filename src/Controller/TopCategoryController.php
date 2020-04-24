<?php

namespace App\Controller;

use App\Entity\TopCategory;
use App\Form\TopCategoryType;
use App\Repository\TopCategoryRepository;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/top/category")
 */
class TopCategoryController extends AbstractController
{
    /**
     * @Route("/new", name="top_category_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $topCategory = new TopCategory();
        $form = $this->createForm(TopCategoryType::class, $topCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($topCategory);
            $entityManager->flush();

            return $this->redirectToRoute('category_index');
        }

        return $this->render('top_category/new.html.twig', [
            'top_category' => $topCategory,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="top_category_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, TopCategory $topCategory, Session $session): Response
    {
        $form = $this->createForm(TopCategoryType::class, $topCategory);
        $form->handleRequest($request);

        if ($session->has('error')) {
            $form->addError(new FormError($session->get('error')));
            $session->remove('error');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('category_index');
        }

        return $this->render('top_category/edit.html.twig', [
            'top_category' => $topCategory,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="top_category_delete", methods={"DELETE"})
     */
    public function delete(Request $request, TopCategory $topCategory, Session $session): Response
    {
        if ($this->isCsrfTokenValid('delete'.$topCategory->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($topCategory);
            try {
                $entityManager->flush();
            } catch(ForeignKeyConstraintViolationException $e) {
                $session->set(
                    'error',
                    'This top category cannot be deleted while sub categories belong to it.'
                );

                return $this->redirect($request->headers->get('referer'));
            }
        }

        return $this->redirectToRoute('category_index');
    }
}
