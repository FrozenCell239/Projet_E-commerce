<?php

namespace App\Controller\AdminPanel;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/products', name: 'app_admin_products_')]
class AdmProductsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('admin/products/index.html.twig');
    }
    #[Route('/add', name: 'add')]
    public function add(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('admin/products/index.html.twig');
    }
    #[Route('/edit/{id}', name: 'edit')]
    public function edit(Product $product): Response
    {
        $this->denyAccessUnlessGranted('PRODUCT_EDIT', $product); //Checking if user is allowed to edit with the voter.
        return $this->render('admin/products/index.html.twig');
    }
    #[Route('/delete/{id}', name: 'delete')]
    public function delete(Product $product): Response
    {
        $this->denyAccessUnlessGranted('PRODUCT_DELETE', $product); //Checking if user is allowed to delete with the voter.
        return $this->render('admin/products/index.html.twig');
    }
}

/**
 * NOTICE :
 * ROLE_PRODUCT_ADMIN peut modifier ou supprimer un produit, mais pas en ajouter.
 * Seul ROLE_ADMIN peut ajouter un produit.
 */