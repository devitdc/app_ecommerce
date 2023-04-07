<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(ContactType::class)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('info', "Merci de nous avoir contacté. Nos équipes vous répondrons dans
            les plus brefs délais.");


        }

        return $this->render('contact/index.html.twig', [
            'title' => 'Nous contacter - '.$this->getParameter('app.brand'),
            'form' => $form->createView()
        ]);
    }
}
