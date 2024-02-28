<?php

namespace App\Controller\Api;

use App\Repository\TopCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/category')]
class CategoryController extends AbstractController
{
    #[Route('/', name: 'category_index', methods: ['GET'])]
    public function index(TopCategoryRepository $topCategoryRepository, SerializerInterface $serializer): JsonResponse
    {
        $topCategories = $topCategoryRepository->findBy([], ['transactionType' => 'desc', 'name' => 'asc']);
        $json = $serializer->serialize($topCategories, 'json');

        return new JsonResponse($json);
    }
}
