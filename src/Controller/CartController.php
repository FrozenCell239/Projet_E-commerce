<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cart', name: 'app_cart_')]
class CartController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(
        SessionInterface $session,
        ProductRepository $productRepository
    )
    {
        # Setting up required informations
        $cart = $session->get('cart', []);
        $cart_data = [];
        $total_price = 0;
        foreach($cart as $product_id => $quantity){
            $product = $productRepository->find($product_id);
            $cart_data[] = [
                'product' => $product,
                'quantity' => $quantity
            ];
            $total_price += $product->getPrice() * $quantity;
        };

        return $this->render('cart/index.html.twig', compact('cart_data', 'total_price'));
    }

    #[Route('/add/{id}', name: 'add')]
    public function addToCart(
        Product $product,
        SessionInterface $session
    )
    {
        $quantity = 1;

        # Getting the product's ID
        $product_id = $product->getId();

        # Getting the existing cart
        $cart = $session->get('cart', []);

        # Remove the product from the cart
        if(empty($cart[$product_id])){
            $cart[$product_id] = $quantity;
        }
        else{ //If it's already in the cart, then we update the quantity.
            $cart[$product_id] += $quantity;
        };

        # Updating the cart
        $session->set('cart', $cart);

        # Redirecting to the cart page
        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/remove/{id}', name: 'remove')]
    public function removeFromCart(
        Product $product,
        SessionInterface $session
    )
    {
        $quantity = 1;

        # Getting the product's ID
        $product_id = $product->getId();

        # Getting the existing cart
        $cart = $session->get('cart', []);

        # Remove the product from the cart
        if(!empty($cart[$product_id])){ //Checking first if the item is in the cart.
            if($cart[$product_id] > 1){ //If there's only one item copy in the cart, then we remove it from the cart.
                $cart[$product_id]--;
            }
            else{ //Else, we decrement the quantity.
                unset($cart[$product_id]);
            };
        };

        # Updating the cart
        $session->set('cart', $cart);

        # Redirecting to the cart page
        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/delete/{id}', name: 'delete')]
    public function deleteFromCart(
        Product $product,
        SessionInterface $session
    )
    {
        $quantity = 1;

        # Getting the product's ID
        $product_id = $product->getId();

        # Getting the existing cart
        $cart = $session->get('cart', []);

        # Remove the product from the cart
        if(!empty($cart[$product_id])){ //Checking first if the item is in the cart.
            unset($cart[$product_id]);
        };

        # Updating the cart
        $session->set('cart', $cart);

        # Redirecting to the cart page
        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/empty', name: 'empty')]
    public function emptyCart(SessionInterface $session)
    {
        $session->remove('cart');

        # Redirecting to the cart page
        return $this->redirectToRoute('app_cart_index');
    }
}