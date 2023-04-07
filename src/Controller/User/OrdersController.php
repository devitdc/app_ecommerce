<?php

namespace App\Controller\User;

use App\Entity\Address;
use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Dompdf\Dompdf;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrdersController extends AbstractController
{
    /**
     * @param OrderRepository $orderRepository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    #[Route('/user/orders', name: 'user_orders')]
    public function index(OrderRepository $orderRepository, PaginatorInterface $paginator, Request $request)
    {
        $orders = $paginator->paginate(
            $orderRepository->findSuccessOrder($this->getUser()),
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('user/orders/index.html.twig', [
            'title' => 'Vos commandes - '.$this->getParameter('app.brand'),
            'orders' => $orders,
        ]);
    }

    /**
     * @param Order $order
     * @param EntityManagerInterface $entityManager
     * @return Response|bool
     * @throws NonUniqueResultException
     */
    #[Route('/user/order/{reference}', name: 'user_order_show')]
    public function createPDFInvoice(Order $order, EntityManagerInterface $entityManager): bool|Response
    {
        $orderReference = $order->getReference();
        $invoice = explode("-", $orderReference);
        $billingAddress = $entityManager->getRepository(Address::class)->findBillingAddress($this->getUser()->getId());

        if (!$billingAddress) {
            $this->addFlash('warning', "Vous n'avez pas configuré d'adresse de facturation. Merci d'en choisir une si vous souhaitez une facture.");
            return $this->redirectToRoute('user_orders');
        }

        $dompdf = new Dompdf([
            'isRemoteEnabled' => true
        ]);
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => FALSE,
                'verify_peer_name' => FALSE,
                'allow_self_signed' => TRUE
            ]
        ]);
        $dompdf->setHttpContext($context);
        $html = $this->renderView('user/orders/invoice.html.twig', [
            'order' => $order,
            'invoiceAddress' => $billingAddress,
            'invoice' => $invoice[1]
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4','portrait');
        $dompdf->render();
        $file = "FA-$invoice[1].pdf";
        $dompdf->stream($file);

        return new Response();
    }

}
