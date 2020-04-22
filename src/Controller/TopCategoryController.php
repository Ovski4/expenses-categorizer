<?php

namespace App\Controller;

use App\Entity\TopCategory;
use App\Form\TopCategoryType;
use App\Repository\TopCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/top/category")
 */
class TopCategoryController extends AbstractController
{
    /**
     * @Route("/", name="top_category_index", methods={"GET"})
     */
    public function index(TopCategoryRepository $topCategoryRepository): Response
    {
        return $this->render('top_category/index.html.twig', [
            'top_categories' => $topCategoryRepository->findBy([], ['name' => 'asc']),
        ]);
    }

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

            return $this->redirectToRoute('top_category_index');
        }

        return $this->render('top_category/new.html.twig', [
            'top_category' => $topCategory,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="top_category_show", methods={"GET"})
     */
    public function show(TopCategory $topCategory): Response
    {
        return $this->render('top_category/show.html.twig', [
            'top_category' => $topCategory,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="top_category_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, TopCategory $topCategory): Response
    {
        $form = $this->createForm(TopCategoryType::class, $topCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('top_category_index');
        }

        return $this->render('top_category/edit.html.twig', [
            'top_category' => $topCategory,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="top_category_delete", methods={"DELETE"})
     */
    public function delete(Request $request, TopCategory $topCategory): Response
    {
        if ($this->isCsrfTokenValid('delete'.$topCategory->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($topCategory);
            $entityManager->flush();
        }

        return $this->redirectToRoute('top_category_index');
    }
}
