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
 * @Route("/category")
 */
class CategoryController extends AbstractController
{
    /**
     * @Route("/", name="category_index", methods={"GET"})
     */
    public function index(TopCategoryRepository $topCategoryRepository): Response
    {
        return $this->render('category/index.html.twig', [
            'top_categories' => $topCategoryRepository->findBy([], ['transactionType' => 'desc', 'name' => 'asc']),
        ]);
    }
}
