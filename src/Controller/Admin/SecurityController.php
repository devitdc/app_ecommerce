<?php

namespace App\Controller\Admin;

use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;
use Egulias\EmailValidator\Validation\RFCValidation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SecurityController extends AbstractController
{
    private HttpClientInterface $client;
    private ValidatorInterface $validator;
    private UserPasswordHasherInterface $passwordHasher;

    /**
     * @param HttpClientInterface $client
     * @param ValidatorInterface $validator
     * @param UserPasswordHasherInterface $passwordHasher
     */
    public function __construct(HttpClientInterface $client, ValidatorInterface $validator, UserPasswordHasherInterface $passwordHasher)
    {
        $this->client = $client;
        $this->validator = $validator;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    #[Route(path: '/signin', name: 'security_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // If user is connected, he is redirected to account page
         if ($this->getUser()) {
             return $this->redirectToRoute('user_account');
         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    /**
     * @return void
     */
    #[Route(path: '/logout', name: 'security_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    public function passwordHasher($user, $password): string
    {
       return $this->passwordHasher->hashPassword($user, $password);
    }

    /**
     * Check if email is valid with DNS and MX record
     * and complies with RFC 5322
     * @param $email
     * @return bool|string
     */
    public function isValidEmail($email): bool|string
    {
        $validator = new EmailValidator();
        $multipleValidations = new MultipleValidationWithAnd([
            new DNSCheckValidation(),
            new RFCValidation(),
        ]);

        if ($validator->isValid($email, $multipleValidations)) {
            return true;
        } else {
            return $validator->getError()->description();
        }
    }

    /**
     * Used when user want to know if his password has been compromised
     * in a data breach with a constraint check
     * @param $password
     * @return int|string
     */
    public function checkPasswordHealth($password): int|string
    {
        $passwordConstraint = [
            new Assert\NotCompromisedPassword([
                'message' => "This password has been leaked in a data breach. Please use another password."
            ]),
        ];

        $errors = $this->validator->validate($password, $passwordConstraint);

        if (!$errors->count()) {
            return 0;
        }

        return $errors[0]->getMessage();
    }

    /**
     * Other example used when user want to know if his password has been compromised
     * in a data breach without a constraint check but using the pwnedpasswords.com API directly
     * @param string $password
     * @return bool
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function isPasswordSafe(string $password): bool
    {
        $passwordHashed = sha1($password);
        $firstFiveChars = substr($passwordHashed, 0, 5);
        $suffix = strtoupper(substr($passwordHashed, 5));

        $url = 'https://api.pwnedpasswords.com/range/' .$firstFiveChars;

        $response = $this->client->request('GET', $url);

        $suffixes = array_map(function ($entry) {
            return substr($entry, 0, strpos($entry, ':'));
        }, preg_split('/\r\n/', $response->getContent()));

        if (in_array($suffix, $suffixes, true)) {
            return false;
        }

        return true;
    }

}
