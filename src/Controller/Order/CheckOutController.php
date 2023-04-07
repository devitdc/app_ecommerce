<?php

namespace App\Controller\Order;

use App\Class\Cart;
use App\Class\Mail;
use App\Entity\Carrier;
use App\Entity\Order;
use App\Service\Stock;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CheckOutController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager)
    {

    }

    #[Route('/order/checkout/success/{stripeSessionId}', name: 'order_checkout_success')]
    public function success(Cart $cart, Order $order, Stock $stock): Response
    {
        $carrierInfo = $this->entityManager->getRepository(Carrier::class)->findOneBy(['name' => $order->getCarrierName()]);
        if ($order->getUser() !== $this->getUser()) {
            return $this->redirectToRoute("home");
        } elseif (!$order->isIsPaid()) {
            $stock->decrease($order);
            $cart->remove('cart');
            $order->setUpdatedAt(new \DateTimeImmutable(null, new \DateTimeZone('Europe/Paris')))
                ->setIsPaid(1)
                ->setDeliveryState(3);
            $this->entityManager->persist($order);
            $this->entityManager->flush();

            $mail = new Mail();
            $options = [
                'orderNumero' => $order->getReference(),
                'customerName' => $order->getDeliveryFirstname(),
                'deliveryDelay' => $carrierInfo->getDelay(),
                'deliveryAddress' => $order->getDeliveryFirstname() . ' '. $order->getDeliveryLastname() . '<br>' .$order->getFullDeliveryAddress(),
                'deliveryType' => $carrierInfo->getName() . ' - ' . $carrierInfo->getType(),
                'orderTotal' => $order->getTotalOrder()
            ];
            $mail->sendCheckOutSuccess($order->getUser()->getEmail(), $order->getUser()->getFirstname(), $options);
        }

        $this->addFlash("success", "Votre commande a été validée.");
        return $this->render('order/checkout/success.html.twig', [
            'title' => 'Confirmation de commande - '.$this->getParameter('app.brand'),
            'order' => $order,
        ]);
    }

    #[Route('/order/checkout/cancel/{stripeSessionId}', name: 'order_checkout_cancel')]
    public function cancel(Order $order): Response
    {
        if ($order->getUser() !== $this->getUser()) {
            return $this->redirectToRoute("home");
        }

        $order->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        $this->addFlash("warning", "Votre paiement n'a pas été validée.");
        return $this->render('order/checkout/cancel.html.twig', [
            'title' => 'Commande non validée - '.$this->getParameter('app.brand'),
            'order' => $order,
        ]);
    }
}
