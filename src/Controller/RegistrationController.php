<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\AccountDTO;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use App\Service\AppService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        private EmailVerifier $emailVerifier,
        private UserPasswordHasherInterface $userPasswordHasher,
        private AppService $appService
    ) {
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request): Response
    {
        if ($this->getUser() !== null) {
            $this->addFlash('registration_active', AccountDTO::REGISTRATION_IS_ACTIVE);
            return $this->redirectToRoute('app_default');
        }
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user = $this->appService->userHashPassword(
                $form->get('plainPassword')->getData(),
                $user,
                $this->userPasswordHasher
            );
            $this->appService->saveEntity($user);
            // generate a signed url and email it to the user
            $this->appService->sendEmailRegistration($this->emailVerifier, $user);
            // do anything else you need here, like send an email
            return $this->redirectToRoute('app_register_ok', ['email' => $user->getEmail()]);
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request): Response
    {
        $id = (int)$request->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $this->appService->getUser($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());
            return $this->redirectToRoute('app_register');
        }

        return $this->redirectToRoute('app_login', ['succes_email' => 1]);
    }

    #[Route('/registerOk', name: 'app_register_ok')]
    public function registerOk(Request $request): Response
    {
        return $this->render('registration/register_ok.html.twig', [
            'email' => $request->get('email'),
        ]);
    }
}
