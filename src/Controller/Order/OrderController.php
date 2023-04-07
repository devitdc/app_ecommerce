<?php

namespace App\Controller\Order;

use App\Class\Cart;
use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Form\OrderType;
use App\Service\ManageOrder;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    #[Route('/order/shipping', name: 'order_home')]
    public function index(Cart $cart): Response
    {
        /**
         * If cart is empty, user can't access his order,
         * and he's redirected to his cart
        */
        if (!$cart->get()) {
            return $this->redirectToRoute('cart_home');
        } elseif (!$this->getUser()->getAddresses()->getValues()) {
            return $this->redirectToRoute('user_address_add');
        }

        $form = $this->createForm(OrderType::class, null, [ 'user' => $this->getUser()]);

        return $this->render('order/index.html.twig', [
            'title' => 'Passer la commande - '.$this->getParameter('app.brand'),
            'form' => $form->createView(),
            'cart' => $cart->getFull()
        ]);
    }

    #[Route('/order/add', name: 'order_add', methods: ['POST'])]
    public function add(Cart $cart, Request $request, EntityManagerInterface $entityManager, ManageOrder $manageOrder): RedirectResponse|Response
    {
        $form = $this->createForm(OrderType::class, null, [ 'user' => $this->getUser()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() ) {
            $date = new \DateTimeImmutable(null, new DateTimeZone('Europe/Paris'));
            $carriers = $form->get('carriers')->getData();
            $delivery = $form->get('addresses')->getData();

            // to check if user has an unpaid order less than 2 days old
            $existingOrder = $entityManager->getRepository(Order::class)->findUserOrderNotPaid($this->getUser());

            if (!$existingOrder) {
                $order = new Order();
                $order->setReference($date->format('Ymd').'-'.uniqid())
                    ->setUser($this->getUser())
                    ->setCreatedAt($date);
            }else {
               $order = $entityManager->getRepository(Order::class)->findOneBy(['reference' => $existingOrder[0]->getReference()]);
               $order->setUpdatedAt($date);
               foreach ($order->getOrderDetails() as $value) {
                    $order->removeOrderDetail($value);
                    $entityManager->flush();
               }
            }

            $manageOrder->addDetails($order, $cart, $carriers, $delivery);

            return $this->render('order/add.html.twig', [
                'title' => 'Valider la commande - '.$this->getParameter('app.brand'),
                'cart' => $cart->getFull(),
                'order' => $order,
                'reference' => $order->getReference(),
                'addressName' => $form->get('addresses')->getData()->getName(),
                'carrierType' => $form->get('carriers')->getData()->getType(),
                'carrierDelay' => $form->get('carriers')->getData()->getDelay(),
            ]);
        }
        return $this->redirectToRoute('cart_home');
    }
}
