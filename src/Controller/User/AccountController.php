<?php

namespace App\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends AbstractController
{
    #[Route('/account/', name: 'user_account')]
    public function index(): Response
    {
        return $this->render('user/account/index.html.twig', [
            'title' => 'Mon espace client - '.$this->getParameter('app.brand'),
        ]);
    }

}
