<?php

namespace App\Controller\User;

use App\Class\Mail;
use App\Controller\Admin\SecurityController;
use App\Entity\User;
use App\Entity\UserResetPassword;
use App\Form\UserResetPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserResetPasswordController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private $security;

    public function __construct(EntityManagerInterface $entityManager, SecurityController $securityController)
    {
        $this->entityManager = $entityManager;
        $this->security = $securityController;
    }
    #[Route(path: '/forgotpassword', name: 'user_reset_password')]
     public function index(AuthenticationUtils $authenticationUtils, Request $request): Response
     {
         $notification = [];

         if ($this->getUser()) {
             return $this->redirectToRoute('home');
         }

         if ($request->request->get('email')) {
             $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $request->request->get('email')]);

             if ($user) {
                 $resetPassword = new UserResetPassword();
                 $resetPassword->setUser($user)
                     ->setToken(uniqid())
                     ->setCreatedAt(new \DateTimeImmutable(null, new \DateTimeZone('Europe/Paris')));
                 $this->entityManager->persist($resetPassword);
                 $this->entityManager->flush();

                 $url = $this->generateUrl('user_reset_password_update', [
                     'token' => $resetPassword->getToken()
                 ]);

                 $mail = new Mail();
                 $mail->sendInfo(
                     $user->getEmail(),
                     $user->getFirstname(),
                     "Le Dressing Français - Mot de passe oublié",
                     $user->getFirstname(),
                     4648678,
                     $this->getParameter('app.website').$url);
                 $notification = [
                     'type' => 'info',
                     'message' => "Vous allez recevoir un email pour réinitialiser votre mot de passe, si vous disposez d'un compte chez <strong>Le Dressing Français</strong>."
                 ];
             }
         }
         // get the login error if there is one
         $error = $authenticationUtils->getLastAuthenticationError();

         return $this->render('user/forgot_password.html.twig', [
             'title' => 'Mot de passe oublié - '.$this->getParameter('app.brand'),
             'notification' => $notification,
             'error' => $error
         ]);
     }

    #[Route(path: '/forgotpassword/update/{token}', name: 'user_reset_password_update', requirements: ['token' => '[0-9a-z]+'])]
    public function update(Request $request, $token)
    {
        $now = new \DateTimeImmutable("Europe/Paris");

        $checkToken = $this->entityManager->getRepository(UserResetPassword::class)->findOneBy(['token' => $token]);
        if (!$checkToken) {
            $notification = [
                'type' => 'warning',
                'message' => "Votre lien de réinitialisation de mot de passe n'existe pas. Veuillez réitérer votre demande."
            ];
            return $this->render('user/forgot_password.html.twig', [
                'title' => 'Mot de passe oublié - '.$this->getParameter('app.brand'),
                'notification' => $notification
            ]);
        }

        $tokenExpire = $checkToken->getCreatedAt()->modify('+ 1 hour');
        if ($now > $tokenExpire) {
            $notification = [
                'type' => 'warning',
                'message' => "Votre demande de nouveau mot de passe a expiré.<br>Veuillez réitérer votre demande."
            ];
            return $this->render('user/forgot_password.html.twig', [
                'title' => 'Mot de passe oublié - '.$this->getParameter('app.brand'),
                'notification' => $notification,
            ]);
        }

        $form = $this->createForm(UserResetPasswordType::class)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form['password']->getData();

            if ($form->getExtraData() && $form->getExtraData()['check_password']) {
                $checkPasswordHealth = $this->security->checkPasswordHealth($newPassword);
                if ($checkPasswordHealth !== 0) {
                    $form['password']->all()['first']->addError(new FormError($checkPasswordHealth));
                    return $this->render('user/reset_password.html.twig', [
                        'title' => 'Nouveau mot de passe - '.$this->getParameter('app.brand'),
                        'form' => $form->createView(),
                        'check_password' => true
                    ]);
                }
            }

            $user = $this->entityManager->getRepository(User::class)->findOneBy(['id' => $checkToken->getUser()->getId()]);
            if ($user) {
                $user->setPassword($this->security->passwordHasher($user, $newPassword));
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                $this->addFlash('success', "Votre mot de passe a été mis à jour. Vous pouvez vous connecter avec votre nouveau mot de passe.");

                return $this->redirectToRoute('security_login');
            }else {
                $this->addFlash('warning', "Aucun utilisateur n'a été trouvé. Veuillez réitérer votre demande ou créer un compte.");
                return $this->redirectToRoute('user_reset_password');
            }

        }

        return $this->render('user/reset_password.html.twig', [
            'title' => 'Nouveau mot de passe - '.$this->getParameter('app.brand'),
            'form' => $form->createView(),
        ]);
    }
}
