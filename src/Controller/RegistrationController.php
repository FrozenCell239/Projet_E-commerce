<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\UserAuthenticator;
use App\Service\JWTService;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        UserAuthenticatorInterface $userAuthenticator,
        UserAuthenticator $authenticator,
        EntityManagerInterface $entityManager,
        SendMailService $mail,
        JWTService $jwt
    ) : Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();
            // Do anything else you need here, like send an email.

            # Generating the user's JWT token
            $header = ['typ' => 'JWT', 'alg' => 'HS256'];
            $payload = ['user_id' => $user->getId()];
            $token = $jwt->generate(
                $header,
                $payload,
                $this->getParameter('app.jwtsecret')
            );

            # Sending the mail
            $mail->send(
                'no-reply@testsite.com',
                $user->getEmail(),
                'E-commerce : activation de votre compte.',
                'register_mail',
                compact('user', 'token')
            );

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/{token}', name: 'app_verify_user')]
    public function verifyUser(
        $token,
        JWTService $jwt,
        UserRepository $userRepository,
        EntityManagerInterface $enttMngrIntrfc
    ) : Response
    {
        # Checking token's validity, expiration, and integrity
        if(
            $jwt->isValid($token) &&
            !$jwt->isExpired($token) &&
            $jwt->check($token, $this->getParameter('app.jwtsecret'))
        ){
            $payload = $jwt->getPayload($token);
            $user = $userRepository->find($payload['user_id']); //Gets token's user.
            if($user && !$user->getIsVerified()){ //Checks if user exists and is still unverified.
                $user->setIsVerified(true);
                $enttMngrIntrfc->flush($user);
                $this->addFlash('success', 'Compte activé avec succès !');
            };
            return $this->redirectToRoute('app_main');
        }
        else{ //https://youtu.be/UrJUn2EL07U?si=JVgaXUEn3yCpJ_fD&t=4459
            $this->addFlash('danger', 'Jeton invalide et/ou expiré.');
            return $this->redirectToRoute('app_login');
        };
    }

    #[Route('resendverif', name: 'app_resend_verification_link')]
    public function resendVerificationLink(
        JWTService $jwt,
        SendMailService $mail,
        UserRepository $userRepository
    ) : Response
    {
        $user = $this->getUser();
        if(!$user){
            $this->addFlash('danger', 'Vous devez être connecté afin de pouvoir effectuer cette action.');
            return $this->redirectToRoute('app_login');
        };
        if($user->getIsVerified()){
            $this->addFlash('warning', 'Cet utilisateur a déjà été vérifié(e).');
            return $this->redirectToRoute('app_main');
        };

        # Generating the user's JWT token
        $header = ['typ' => 'JWT', 'alg' => 'HS256'];
        $payload = ['user_id' => $user->getId()];
        $token = $jwt->generate(
            $header,
            $payload,
            $this->getParameter('app.jwtsecret')
        );

        # Sending the mail
        $mail->send(
            'no-reply@testsite.com',
            $user->getEmail(),
            'E-commerce : activation de votre compte.',
            'register_mail',
            compact('user', 'token')
        );

        $this->addFlash('success', 'Le nouvel email de vérification a été envoyé !');
        return $this->redirectToRoute('app_main');
    }
}