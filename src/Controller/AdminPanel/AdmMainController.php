<?php

namespace App\Controller\AdminPanel;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin', name: 'app_admin_')]
class AdmMainController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index() : Response {
        return $this->render('admin/index.html.twig');
    }
}