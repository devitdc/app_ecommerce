<?php

namespace App\Controller\Stripe;

use App\Entity\Order;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class StripeController extends AbstractController
{
    #[Route('/order/payment/{reference}', name: 'stripe_create_session', methods: ['POST'])]
    public function createSession(Order $order, ProductRepository $productRepository, EntityManagerInterface $entityManager): RedirectResponse
    {
        $products_for_stripe = [];
        $YOUR_DOMAIN = $this->getParameter('app.website');

        foreach ($order->getOrderDetails()->getValues() as $product) {
            $product_object = $productRepository->findOneBy(['name' => $product->getProduct()]);

            $products_for_stripe[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $product->getPrice()*100,
                    'product_data' => [
                        'name' => $product->getProduct(),
                        'images' => [$YOUR_DOMAIN . "/images/uploads/" . $product_object->getImage()],
                    ]
                ],
                'quantity' => $product->getQuantity(),
            ];
        }

        $products_for_stripe[] = [
            'price_data' => [
                'currency' => 'eur',
                'unit_amount' => $order->getCarrierPrice()*100,
                'product_data' => [
                    'name' => $order->getCarrierName(),
                    'images' => [$YOUR_DOMAIN],
                ]
            ],
            'quantity' => 1,
        ];

        // This is your test secret API key.
        Stripe::setApiKey('sk_test_51MeHNLBoHQosuazKIqDH27rdhrWkcvbKDQdpWuv8Vn8TeE2xYKVK2HfxiDFi2qSIDGbbiKXqNBVed6ZO9Z0PZ0Fq002fQmhuMP');

        $checkout_session = Session::create([
            'customer_email' => $this->getUser()->getEmail(),
            'line_items' => [
                $products_for_stripe
            ],
            'mode' => 'payment',
            "payment_method_types" => [
                'card'
            ],
            'success_url' => $YOUR_DOMAIN . '/order/checkout/success/{CHECKOUT_SESSION_ID}',
            'cancel_url' => $YOUR_DOMAIN . '/order/checkout/cancel/{CHECKOUT_SESSION_ID}',
        ]);

        $order->setStripeSessionId($checkout_session->id);
        $entityManager->persist($order);
        $entityManager->flush();

        return $this->redirect($checkout_session->url);
    }
}
