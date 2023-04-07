<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

class Stock
{

    public function __construct(
        private EntityManagerInterface $entityManager
    )
    {

    }

    public function decrease(Order $order): void
    {
        $orderDetails = $order->getOrderDetails()->getValues();
        foreach ($orderDetails as $detail) {
            $product = $this->entityManager->getRepository(Product::class)->findOneBy(['name' => $detail->getProduct()]);
            $product->setStock($product->getStock() - $detail->getQuantity());
            $this->entityManager->persist($product);
            $this->entityManager->flush();
        }
    }
    
}