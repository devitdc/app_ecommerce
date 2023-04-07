<?php

namespace App\Controller\User;

use App\Class\Cart;
use App\Entity\Address;
use App\Form\UserAddAddressType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AddressController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    )
    {

    }
    #[Route('/user/address', name: 'user_address_home')]
    public function home(): Response
    {
        return $this->render('user/address/index.html.twig', [
            'title' => 'Mes adresses - '.$this->getParameter('app.brand'),
        ]);
    }

    #[Route('/user/address/add', name: 'user_address_add')]
    public function add(Cart $cart, Request $request): Response
    {
        $address = new Address();
        $form = $this->createForm(UserAddAddressType::class, $address)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $checkBilling = $this->checkBillingState($form);
            if ($checkBilling) {
                $address->setUser($this->getUser());
                $this->entityManager->persist($address);
                $this->entityManager->flush();
                $this->addFlash('success', "L'adresse a bien été ajoutée.");

                // Redirect to cart if there are products
                if ($cart->get()) {
                    return $this->redirectToRoute('order_home');
                }

                return $this->redirectToRoute('user_address_home');
            } else {
                $this->addFlash("warning", "Une erreur est survenue. Veuillez vérifier vos informations.");
                $form['isBilling']->addError(new FormError("Vous disposez déjà d'une adresse de facturation. Vous devez d'abord la désactiver pour utiliser celle-là."));
            }
        }

        return $this->render('user/address/form.html.twig', [
            'title' => 'Ajouter une adresse - '.$this->getParameter('app.brand'),
            'editMode' => false,
            'form' => $form->createView()
        ]);
    }

    #[Route('/user/address/update/{id}', name: 'user_address_update')]
    public function update(Request $request, Address $address): Response
    {
        $form = $this->createForm(UserAddAddressType::class, $address)->handleRequest($request);

        if (!$address || $address->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('user_address_home');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $checkBilling = $this->checkBillingState($form);
            if ($checkBilling) {
                $this->entityManager->persist($address);
                $this->entityManager->flush();
                $this->addFlash('success', "L'adresse a bien été mis à jour.");
                return $this->redirectToRoute('user_address_home');
            } else {
                $this->addFlash("warning", "Une erreur est survenue. Veuillez vérifier vos informations.");
                $form['isBilling']->addError(new FormError("Vous disposez déjà d'une adresse de facturation. Vous devez d'abord la désactiver pour utiliser celle-là."));
            }

        }

        return $this->render('user/address/form.html.twig', [
            'title' => 'Modifier votre adresse - '.$this->getParameter('app.brand'),
            'editMode' => true,
            'form' => $form->createView()
        ]);
    }

    #[Route('/user/address/delete/{id}', name: 'user_address_delete', methods: ['DELETE', 'POST'])]
    public function delete(Address $address, Request $request): Response
    {
        if (!$address->getUser()) {
            return $this->redirectToRoute('user_address_home');
        } elseif ($address->getUser() === $this->getUser() || $this->isCsrfTokenValid("DEL".$address->getId(), $request->get("_token"))) {
            $this->entityManager->remove($address);
            $this->entityManager->flush();
            $this->addFlash('success', "L'adresse a bien été supprimée.");
            return $this->redirectToRoute('user_address_home');
        }

        return $this->redirectToRoute('user_address_home');
    }

    public function checkBillingState($form): bool
    {
        $findBillingAddress = $this->entityManager->getRepository(Address::class)->findBillingAddress($this->getUser()->getId());
        if ($form->getData()->isIsBilling() && $findBillingAddress) {
            return false;
        }

        return true;
    }
}
