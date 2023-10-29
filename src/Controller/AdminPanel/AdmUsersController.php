<?php

namespace App\Controller\AdminPanel;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/users', name: 'app_admin_users_')]
class AdmUsersController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findBy([], ['lastname' => 'asc']);
        return $this->render('admin/users/index.html.twig', compact('users'));
    }
}
