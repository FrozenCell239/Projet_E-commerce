<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderDetail;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

#[Route('/order', name: 'app_order_')]
class OrderController extends AbstractController
{
    #[Route('/new', name: 'new')]
    public function newOrder(
        SessionInterface $session,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $cart = $session->get('cart', []);

        # Checking if the cart is empty
        if($cart === []){
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('app_main');
        };

        # Creating the order
        $order = new Order();
        $order
            ->setUser($this->getUser())
            ->setReference(uniqid());
        ;
        foreach($cart as $item => $quantity){
            $product = $productRepository->find($item);
            $price = $product->getPrice();

            # Creating the order details
            $order_details = new OrderDetail();
            $order_details
                ->setProduct($product)
                ->setPrice($price)
                ->setQuantity($quantity)
            ;
            $order->addOrderDetail($order_details);
        };

        # Storing the order into the database
        try{
            $entityManager->persist($order);
            $entityManager->flush();
            $this->addFlash('info', 'Commande crée avec succès !');
            $session->remove('cart');
        }
        catch(\Throwable $th){
            $this->addFlash('danger', 'Une erreur est survenue lors de la création de votre commande.');
            throw $th;
        };

        return $this->redirectToRoute('app_main');
    }
}
