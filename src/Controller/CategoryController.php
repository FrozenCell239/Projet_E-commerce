<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/category', name: 'app_category_')]
class CategoryController extends AbstractController
{
    #[Route('/{slug}', name: 'list')]
    public function list(
        Category $category,
        ProductRepository $productRepository,
        Request $request
    ): Response
    {
        # Get page number in the URL
        $page = $request->query->getInt('page', 1);

        //$products = $category->getProducts();
        $products = $productRepository->paginatedFindProducts($page, $category->getSlug(), 2);
        
        return $this->render(
            'category/list.html.twig', 
            compact(
                'category',
                'products'
            )
        );
    }
}