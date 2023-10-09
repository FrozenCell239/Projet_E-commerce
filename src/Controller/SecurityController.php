<?php

namespace App\Controller;

use App\Form\ResetPasswordRequestType;
use App\Form\ResetPasswordType;
use App\Repository\UserRepository;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_main');
        }

        $error = $authenticationUtils->getLastAuthenticationError(); // Gets the login error if there is one;
        $lastUsername = $authenticationUtils->getLastUsername(); // Last username entered by the user.

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/forgotten_password', name: 'app_forgotten_password')]
    public function forgottenPassword(
        Request $request,
        UserRepository $userRepository,
        TokenGeneratorInterface $tokenGeneratorInterface,
        EntityManagerInterface $entityManagerInterface,
        SendMailService $mail
    ) : Response 
    {
        $form = $this->createForm(ResetPasswordRequestType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $user = $userRepository->findOneByEmail($form->get('email')->getData()); //Gets the user using its mail address.
            if($user){
                # Creating the reset token
                $token = $tokenGeneratorInterface->generateToken();
                $user->setResetToken($token);
                $entityManagerInterface->persist($user); //I should add a try-catch here later !
                $entityManagerInterface->flush();

                # Generating a password reset link
                $url = $this->generateUrl(
                    'app_password_reset',
                    ['token' => $token],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                # Creating mail's datas
                $context = compact('url', 'user');

                # Sending the mail
                $mail->send(
                    'no-reply@testsite.com',
                    $user->getEmail(),
                    'Réinitialisation de votre mot de passe',
                    'password_reset_request_mail',
                    $context
                );
                $this->addFlash('info', 'Un mail de réinitialisation vous a été envoyé.');
                return $this->redirectToRoute('app_login');
            }
            else{
                $this->addFlash('danger', 'Un problème est survenu.');
                return $this->redirectToRoute('app_login');
            };
        };
        return $this->render('security/password_reset_request.html.twig', [
            'requestResetPassForm' => $form->createView()
        ]);
    }

    #[Route('/forgotten_password/{token}', name: 'app_password_reset')]
    public function resetPassword(
        string $token,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManagerInterface,
        UserPasswordHasherInterface $userPasswordHasherInterface
    ) : Response {
        $user = $userRepository->findOneByResetToken($token);
        if($user){
            $form = $this->createForm(ResetPasswordType::class);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $user
                    ->setResetToken('') //Erases the token.
                    ->setPassword(
                        $userPasswordHasherInterface->hashPassword(
                            $user,
                            $form->get('password')->getData()
                        )
                    )
                ;
                $entityManagerInterface->persist($user);
                $entityManagerInterface->flush();
                $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès.');
                return $this->redirectToRoute('app_login');
            };
            return $this->render('security/password_reset.html.twig', [
                'resetPassForm' => $form->createView()
            ]);
        };
        $this->addFlash('danger', 'Jeton de réinitialisation invalide.');
        return $this->redirectToRoute('app_login');
    }
}