<?php

namespace App\Controller\User;

use App\Class\Mail;
use App\Controller\Admin\SecurityController;
use App\Entity\User;
use App\Form\UserRegisterType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class RegisterController extends AbstractController
{
    public function __construct(
        private VerifyEmailHelperInterface $helper,
    )
    {

    }
    
    #[Route('/register', name: 'user_register')]
    public function register(Request $request, EntityManagerInterface $entityManager, SecurityController $securityController): Response
    {
        $user = new User();
        $form = $this->createForm(UserRegisterType::class, $user)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $isValidEmail = $securityController->isValidEmail($user->getUserIdentifier());
            if ($isValidEmail) {
                if ($form->getExtraData() && $form->getExtraData()['check_password']) {
                    $checkPasswordHealth = $securityController->checkPasswordHealth($form->get('password')->getData());
                    if ($checkPasswordHealth !== 0) {
                        $form['password']->all()['first']->addError(new FormError($checkPasswordHealth));
                        return $this->render('user/register.html.twig', [
                            'title' => 'Inscription - '.$this->getParameter('app.brand'),
                            'form' => $form->createView(),
                            'check_password' => true
                        ]);
                    }
                }

                $user->setPassword($securityController->passwordHasher($user, $user->getPassword()))
                    ->setRoles(['ROLE_USER'])
                    ->setIsVerified(false);
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', "Votre inscription a bien été effectuée. Vous avez reçu un email de validation.");

                $this->sendActivationMail($user);
            } else {
                return $this->render('user/register.html.twig', [
                    'title' => 'Inscription - '.$this->getParameter('app.brand'),
                    'form' => $form->createView(),
                    'emailError' => $isValidEmail
                ]);
            }
            return $this->redirectToRoute("security_login");
        }

        return $this->render('user/register.html.twig', [
            'title' => 'Inscription - '.$this->getParameter('app.brand'),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/verify', name: 'user_register_confirmation')]
    public function verifyUserEmail(Request $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        $id = $request->get('id'); // retrieve the user id from the url
        // Verify the user id exists and is not null
        if (null === $id) {
            $this->addFlash('danger', 'Utilisateur inconnu. Veuillez créer un compte.');
            return $this->redirectToRoute('user_register');
        }

        $user = $entityManager->getRepository(User::class)->findOneBy(['id' => $id]);
        // Ensure the user exists in persistence
        if (null === $user) {
            $this->addFlash('danger', 'Utilisateur inconnu. Veuillez créer un compte.');
            return $this->redirectToRoute('user_register');
        }

        // Do not get the User's Id or Email Address from the Request object
        try {
            $this->helper->validateEmailConfirmation($request->getUri(), $user->getId(), $user->getUserIdentifier());
        } catch (VerifyEmailExceptionInterface $e) {
            $this->addFlash('danger', $e->getReason()." A new activation link has just been sent to you by email.");
            $this->sendActivationMail($user);
            return $this->redirectToRoute('security_login');
        }

        $user->setIsVerified(true);
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Your e-mail address has been verified.');
        return $this->redirectToRoute('security_login');
    }

    public function sendActivationMail($user)
    {
        $signatureComponents = $this->helper->generateSignature(
            'user_register_confirmation',
            $user->getId(),
            $user->getUserIdentifier(),
            ['id' => $user->getId()] // add the user's id as an extra query param
        );
        $mail = new Mail();
        $mail->sendInfo($user->getEmail(), $user->getFirstname(), "Le Dressing Français - Activez votre compte", $user->getFirstname(), 4635500, $signatureComponents->getSignedUrl());
    }

}
