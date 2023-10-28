<?php

namespace App\Controller\AdminPanel;

use App\Entity\Image;
use App\Entity\Product;
use App\Form\ProductFormType;
use App\Repository\ProductRepository;
use App\Service\PictureService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/products', name: 'app_admin_products_')]
class AdmProductsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();
        return $this->render('admin/products/index.html.twig', compact('products'));
    }

    #[Route('/add', name: 'add')]
    public function add(
        Request $request,
        EntityManagerInterface $entity_manager,
        SluggerInterface $slugger,
        PictureService $pictureService
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $product = new Product(); //Creating the new product.
        $product_form = $this->createForm(ProductFormType::class, $product); //Creating the new product's form.
        $product_form->handleRequest($request); //Handling the the form request.
        if($product_form->isSubmitted() && $product_form->isValid()){ //Checking if the form is both submitted and valid.
            # Getting images
            $images = $product_form->get('images')->getData();
            foreach($images as $image){
                $folder = 'products'; //Destination folder.
                $file = $pictureService->addPicture($image, $folder, 300, 300); //Calling images adding service.
                $new_image = new Image();
                $new_image->setName($file);
                $product->addImage($new_image);
            };

            # Setting up the slug
            $product->setSlug($slugger->slug($product->getName())); //Creating the slug.

            # Putting the new product into database
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
        SluggerInterface $slugger,
        PictureService $pictureService
    ): Response
    {
        $this->denyAccessUnlessGranted('PRODUCT_EDIT', $product); //Checking if user is allowed to edit with the voter.
        $product_form = $this->createForm(ProductFormType::class, $product); //Creating the new product's form.
        $product_form->handleRequest($request); //Handling the the form request.
        if($product_form->isSubmitted() && $product_form->isValid()){ //Checking if the form is both submitted and valid.
            # Getting images
            $images = $product_form->get('images')->getData();
            foreach($images as $image){
                $folder = 'products'; //Destination folder.
                $file = $pictureService->addPicture($image, $folder, 300, 300); //Calling images adding service.
                $new_image = new Image();
                $new_image->setName($file);
                $product->addImage($new_image);
            };
            
            # Setting up the slug
            $product->setSlug($slugger->slug($product->getName()));

            # Saving into database
            $entity_manager->persist($product);
            $entity_manager->flush();
            $this->addFlash('success', 'Produit modifié avec succès !');
            return $this->redirectToRoute('app_admin_products_index');
        };
        return $this->render('admin/products/edit.html.twig', [
            'product_form' => $product_form->createView(),
            'product' => $product
        ]);
    }

    #[Route('/delete/{id}', name: 'delete')]
    public function delete(Product $product): Response
    {
        $this->denyAccessUnlessGranted('PRODUCT_DELETE', $product); //Checking if user is allowed to delete with the voter.
        return $this->render('admin/products/index.html.twig');
    }

    #[Route('/delete/image/{id}', name: 'image_delete', methods: ['DELETE'])]
    public function imageDelete(
        Image $image,
        Request $request,
        EntityManagerInterface $entityManager,
        PictureService $pictureService
    ): JsonResponse
    {
        # Get request content
        $data = json_decode($request->getContent(), true);
        if($this->isCsrfTokenValid('delete'.$image->getId(), $data['_token'])){ //Checks token's validity.
            $name = $image->getName();
            try{
                $pictureService->deletePicture($name, 'products', 300, 300);
            }
            catch(\Throwable $th){
                return new JsonResponse(['error' => 'Erreur de suppression.'], 400);
            };

            # Deleting image from database
            $entityManager->remove($image);
            $entityManager->flush();

            return new JsonResponse(['success' => true], 200);
        };
        return new JsonResponse(['error' => 'Jeton invalide.'], 400);
    }
}