<?php

namespace App\Controller\Cart;

use App\Class\Cart;
use App\Entity\Product;
use App\Service\ManageOrder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'cart_home')]
    public function index(Cart $cart): Response
    {
        return $this->render('cart/index.html.twig', [
            'title' => 'Mon panier - '.$this->getParameter('app.brand'),
            'cart' => $cart->getFull()
        ]);
    }

    #[Route('/cart/add/{id}', name: 'cart_add')]
    public function add(Cart $cart, EntityManagerInterface $entityManager, $id = null): RedirectResponse
    {
        $product = $entityManager->getRepository(Product::class)->findProductWithStock($id);

        if ($product) {
            $cart->add($id);
            $this->addFlash("success", "L'article a été ajouté à votre panier.");
        } else {
            $this->addFlash("warning", "L'article demandé n'existe pas ou n'est plus en stock.");
        }

        return (isset($_SERVER['HTTP_REFERER'])) ? $this->redirect($_SERVER['HTTP_REFERER']) : $this->redirectToRoute('product_list');
    }

    #[Route('/cart/remove', name: 'cart_remove')]
    public function remove(Cart $cart): Response
    {
        $cart->remove();

        return $this->redirectToRoute('cart_home');
    }

    #[Route('/cart/decrease/{id}', name: 'cart_decrease_quantity')]
    public function decreaseProductQuantity(Cart $cart, $id): Response
    {
        $cart->decreaseProductQuantity($id);

        return (isset($_SERVER['HTTP_REFERER'])) ? $this->redirect($_SERVER['HTTP_REFERER']) : $this->redirectToRoute('product_list');
    }

    #[Route('/cart/remove/{id}', name: 'cart_remove_product')]
    public function removeProduct(Cart $cart, $id, ManageOrder $manageOrder): Response
    {
        $delete = $cart->removeProduct($id);
        if ($delete) {
            $this->addFlash('success', "L'article a été retiré du panier.");
        } else {
            $this->addFlash('danger', "Une erreur est survenue. Votre panier n'a pas été mis à jour.");
        }
        return $this->redirectToRoute('cart_home');
    }
}
