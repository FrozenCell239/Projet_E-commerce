<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/profile', name: 'app_profile_')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        //!BUG : L'utilisateur ne doit pas accéder à cette page s'il n'est pas connecté.
        return $this->render('profile/index.html.twig', [
            'controller_name' => 'Mon profil',
        ]);
    }
    #[Route('/orders', name: 'orders')]
    public function orders(): Response
    {
        return $this->render('profile/index.html.twig', [
            'controller_name' => 'Mes commandes',
        ]);
    }
}
