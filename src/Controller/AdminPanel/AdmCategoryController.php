<?php

namespace App\Controller\AdminPanel;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/categories', name: 'app_admin_categories_')]
class AdmCategoryController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(CategoryRepository $categoryRepository) : Response {
        $categories = $categoryRepository->findBy([], ['category_order' => 'asc']);
        return $this->render('admin/categories/index.html.twig', compact('categories'));
    }
}