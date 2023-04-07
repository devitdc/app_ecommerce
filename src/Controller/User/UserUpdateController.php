<?php

namespace App\Controller\User;

use App\Controller\Admin\SecurityController;
use App\Form\UserUpdateInfosType;
use App\Form\UserUpdatePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserUpdateController extends AbstractController
{

    private SecurityController $security;

    public function __construct(SecurityController $securityController)
    {
        $this->security = $securityController;
    }

    #[Route('/account/infos', name: 'user_update_home')]
    public function homeAccount(): Response
    {
        return $this->render('user/account/infos.html.twig', [
            'title' => 'Mon compte - '.$this->getParameter('app.brand'),
        ]);
    }

    #[Route('/account/update/infos/', name: 'user_update_account')]
    public function updateAccount(Request $request, EntityManagerInterface $entityManager): RedirectResponse|Response
    {
        $emailError = '';
        $user = $this->getUser();

        $formEdit = $this->createForm(UserUpdateInfosType::class, $user, [
            'action' => $this->generateUrl('user_update_account')
        ])->handleRequest($request);

        if ($formEdit->isSubmitted() && $formEdit->isValid()) {
            $isValidEmail = $this->security->isValidEmail($formEdit['email']->getData());
            if ($isValidEmail === true) {
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', "Vos informations personnelles ont été mis à jour.");

                return $this->redirectToRoute('user_update_account');
            } else {
                $emailError = $isValidEmail;
            }
        }

        return $this->render('user/account/update_account.html.twig', [
            'title' => 'Modifier vos informations - '.$this->getParameter('app.brand'),
            'user' => $user,
            'emailError' => $emailError,
            'formEdit' => $formEdit->createView(),
        ]);
    }

    #[Route('/account/update/password/', name: 'user_update_password')]
    public function updatePassword(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHashed): RedirectResponse|Response
    {
        $user = $this->getUser();
        $form = $this->createForm(UserUpdatePasswordType::class, $user, [
            'action' => $this->generateUrl('user_update_password')
        ])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form['new_password']->getData();

            if ($passwordHashed->isPasswordValid($user, $form['old_password']->getData())) {
                if ($form->getExtraData() && $form->getExtraData()['check_password']) {
                    $checkPasswordHealth = $this->security->checkPasswordHealth($newPassword);
                    if ($checkPasswordHealth !== 0) {
                        $form['new_password']->all()['first']->addError(new FormError($checkPasswordHealth));
                        return $this->render('user/account/update_password.html.twig', [
                            'title' => 'Modifier le mot de passe - '.$this->getParameter('app.brand'),
                            'user' => $user,
                            'form' => $form->createView(),
                            'check_password' => true
                        ]);
                    }
                }

                /*if ($form['check_password']->getData()) {
                    $checkPasswordHealth = $this->security->checkPasswordHealth($newPassword);
                    if ($checkPasswordHealth !== 0) {
                        $form['new_password']->all()['first']->addError(new FormError($checkPasswordHealth));
                        return $this->render('user/account/update_password.html.twig', [
                            'title' => "Modifier le mot de passe - Le Dressing Français",
                            'user' => $user,
                            'form' => $form->createView()
                        ]);
                    }
                }*/

                $user->setPassword($this->security->passwordHasher($user, $newPassword));
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', "Votre mot de passe a été mis à jour.");
            } else {
                $form->get('old_password')->addError(new FormError('Le mot de passe ne correspond pas'));
                return $this->render('user/account/update_password.html.twig', [
                    'title' => 'Modifier le mot de passe - '.$this->getParameter('app.brand'),
                    'user' => $user,
                    'form' => $form->createView()
                ]);
            }
            return $this->redirectToRoute('user_update_password');
        }

        return $this->render('user/account/update_password.html.twig', [
            'title' => 'Modifier le mot de passe - '.$this->getParameter('app.brand'),
            'user' => $user,
            'form' => $form->createView()
        ]);

    }
}
