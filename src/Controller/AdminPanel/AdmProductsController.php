<?php

namespace App\Controller\AdminPanel;

use App\Entity\Product;
use App\Form\ProductFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/products', name: 'app_admin_products_')]
class AdmProductsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('admin/products/index.html.twig');
    }
    #[Route('/add', name: 'add')]
    public function add(
        Request $request,
        EntityManagerInterface $entity_manager,
        SluggerInterface $slugger
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $product = new Product(); //Creating the new product.
        $product_form = $this->createForm(ProductFormType::class, $product); //Creating the new product's form.
        $product_form->handleRequest($request); //Handling the the form request.
        if($product_form->isSubmitted() && $product_form->isValid()){ //Checking if the form is both submitted and valid.
            $product
                ->setSlug($slugger->slug($product->getName())) //Creating the slug.
                ->setPrice($product->getPrice() * 100) //Fixing the price.
            ;
            $entity_manager->persist($product);
            $entity_manager->flush();
            $this->addFlash('success', 'Produit ajouté avec succès !');
            return $this->redirectToRoute('app_admin_products_index');
        };
        return $this->render('admin/products/add.html.twig', compact('product_form'));
    }
    #[Route('/edit/{id}', name: 'edit')]
    public function edit(
        Product $product,
        Request $request,
        EntityManagerInterface $entity_manager,
        SluggerInterface $slugger
    ): Response
    {
        $this->denyAccessUnlessGranted('PRODUCT_EDIT', $product); //Checking if user is allowed to edit with the voter.
        $product->setPrice($product->getPrice() / 100); //Fixing the price.
        $product_form = $this->createForm(ProductFormType::class, $product); //Creating the new product's form.
        $product_form->handleRequest($request); //Handling the the form request.
        if($product_form->isSubmitted() && $product_form->isValid()){ //Checking if the form is both submitted and valid.
            $product
                ->setSlug($slugger->slug($product->getName())) //Creating the slug.
                ->setPrice($product->getPrice() * 100) //Fixing the price.
            ;
            $entity_manager->persist($product);
            $entity_manager->flush();
            $this->addFlash('success', 'Produit modifié avec succès !');
            return $this->redirectToRoute('app_admin_products_index');
        };
        return $this->render('admin/products/edit.html.twig', compact('product_form'));
    }
    #[Route('/delete/{id}', name: 'delete')]
    public function delete(Product $product): Response
    {
        $this->denyAccessUnlessGranted('PRODUCT_DELETE', $product); //Checking if user is allowed to delete with the voter.
        return $this->render('admin/products/index.html.twig');
    }
}