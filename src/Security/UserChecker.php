<?php

namespace App\Security;

use App\Controller\User\RegisterController;
use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function __construct(private RegisterController $registerController)
    {
    }

    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isIsVerified()) {
            $this->registerController->sendActivationMail($user);
            throw new CustomUserMessageAccountStatusException('Your user account is not verified. A new activation link has just been sent to you by email.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        /*if (!$user->isIsActive()) {
            throw new AccountExpiredException('Your user account has been disabled.');
        }*/
    }
}