<?php

namespace App\Service;

use App\Class\Cart;
use App\Entity\Order;
use App\Entity\OrderDetails;
use Doctrine\ORM\EntityManagerInterface;

class ManageOrder
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    )
    {
    }

    /**
     * Used to insert product in OrderDetails entity
     * when it's a new order or an existing one
     * @param Order $order
     * @param Cart $cart
     * @param $carriers
     * @param $delivery
     * @return void
     */
    public function addDetails(Order $order, Cart $cart, $carriers, $delivery): void
    {
        $products = $cart->getFull();

        foreach ($products as $product) {
            $orderDetails = new OrderDetails();
            $orderDetails->setMyOrder($order)
                ->setProduct($product['product']->getName())
                ->setQuantity($product['quantity'])
                ->setPrice($product['product']->getPrice())
                ->setTotal($product['product']->getPrice()*$product['quantity'])
                ->setImageName($product['product']->getImage())
                ->setProductId($product['product']->getId());
            $this->entityManager->persist($orderDetails);
        }

        if ($delivery->getCompany()) {
            $order->setDeliveryCompany($delivery->getCompany());
        } elseif ($delivery->getAddress2()) {
            $order->setDeliveryAddress2($delivery->getAddress2());
        }

        $order->setCarrierName($carriers->getName())
            ->setCarrierPrice($carriers->getPrice())
            ->setIsPaid(0)
            ->setDeliveryState(0)
            ->setDeliveryFirstname($delivery->getFirstname())
            ->setDeliveryLastname($delivery->getLastname())
            ->setDeliveryAddress1($delivery->getAddress1())
            ->setDeliveryPostalCode($delivery->getPostalCode())
            ->setDeliveryCity($delivery->getCity())
            ->setDeliveryCountry($delivery->getCountry())
            ->setDeliveryPhone($delivery->getPhone());

        $this->entityManager->persist($order);
        $this->entityManager->flush();
    }

}
