<?php

namespace App\Class;

use App\Entity\Product;
use App\Service\ManageOrder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class Cart
{
    public function __construct(
        private RequestStack $requestStack,
        private EntityManagerInterface $entityManager,
        private FlashBagInterface $flashBag,
        )
    {

    }

    public function initSession()
    {
        return $this->requestStack->getSession();
    }

    public function add($id)
    {
        $cart = $this->initSession()->get('cart', []);

        if (!empty($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }

        return $this->initSession()->set('cart', $cart);
    }

    public function get()
    {
        return $this->initSession()->get('cart');
    }

    public function getFull(): array
    {
        $item = [];
        $cart = $this->get();

        if ($cart) {
            foreach ($cart as $id => $quantity ) {
                $product = $this->entityManager->getRepository(Product::class)->findOneBy(['id' => $id]);
                if ($product) {
                    if ($quantity > $product->getStock()) {
                        $quantity = $product->getStock();
                        $cart[$id] = $quantity;
                        $this->initSession()->set('cart',$cart);
                    }
                    $item[] = [
                        'product' => $product,
                        'quantity' => $quantity
                    ];
                }else {
                    $this->flashBag->add('error', "L'article dans votre panier n'est plus présent. Celui-ci a été retiré.");
                    $this->removeProduct($id);
                }
            }
        }

        return $item;
    }

    public function remove($name)
    {
        return $this->initSession()->remove($name);
    }

    public function removeProduct($id): bool
    {
        $cart = $this->initSession()->get('cart', []);
        $cartItemNumber = count($cart);
        unset($cart[$id]);

        if (count($cart) < $cartItemNumber) {
            $this->initSession()->set('cart', $cart);
            return true;
        } else {
            return false;
        }
    }

    public function decreaseProductQuantity($id)
    {
        $cart = $this->initSession()->get('cart', []);

        if ($cart[$id] > 1) {
            $cart[$id]--;
            $this->initSession()->set('cart', $cart);
        } else {
            return $this->removeProduct($id);
        }
    }
}