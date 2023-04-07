<?php

namespace App\Controller\Admin;

use App\Entity\Carrier;
use App\Entity\Category;
use App\Entity\Header;
use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\OrderDetailsRepository;
use App\Repository\OrderRepository;
use App\Service\ChartJS;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private OrderRepository $orderRepository,
        private OrderDetailsRepository $orderDetailsRepository,
        private ChartJS $chartJS
    )
    {
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    #[Route('/cockpit', name: 'admin')]
    public function index(): Response
    {
        $totalPaidOrders = $this->orderRepository->totalPaidOrders();
        $totalPendingOrders = $this->orderRepository->totalPendingOrders();
        $totalProgressDeliveryOrders = $this->orderRepository->totalProgressDeliveryOrders();
        $totalDeliveredOrders = $this->orderRepository->totalDeliveredOrders();
        $dayAmount = $this->orderRepository->dayAmount();
        $monthAmount = $this->orderRepository->monthAmount();
        $yearAmount = $this->orderRepository->yearAmount();
        $lastOrders = $this->orderRepository->findLastOrders();
        $bestSellingProducts = $this->orderDetailsRepository->findBestSellingProducts();
        $monthlySalesOverview = $this->orderRepository->salesOverviewByMonth();

        return $this->render('bundles/EasyAdminBundle/dashboard.html.twig', [
            'totalPaidOrders' => $totalPaidOrders,
            'totalPendingOrders' => $totalPendingOrders,
            'totalProgressDeliveryOrders' => $totalProgressDeliveryOrders,
            'totalDeliveredOrders' => $totalDeliveredOrders,
            'dayAmount' => $dayAmount,
            'monthAmount' => $monthAmount,
            'yearAmount' => $yearAmount,
            'lastOrders' => $lastOrders,
            'chartBestSellingProducts' => $this->chartJS->createPieChart('Product', $bestSellingProducts),
            'chartMonthlySalesOverview' => $this->chartJS->createLineCircleChart('Total Orders (Tax excl.)', $monthlySalesOverview)
        ]);

        //return parent::index();
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Le Dressing Français');
    }

    public function configureCrud(): Crud
    {
        return parent::configureCrud()
            ->overrideTemplate('crud/field/id', 'bundles/EasyAdminBundle/field/id_with_icon.html.twig');;
    }

    public function configureActions(): Actions
    {
        return parent::configureActions()
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, static function (Action $action) {
                return $action->setIcon('fa fa-eye')->setLabel('Detail');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, static function (Action $action) {
                return $action->setIcon('fa fa-edit');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, static function (Action $action) {
                return $action->setIcon('fa fa-trash');
            })
            ->update(Crud::PAGE_DETAIL, Action::EDIT, static function (Action $action) {
                return $action->setIcon('fa fa-edit')
                    ->setCssClass('btn');
            })
            ->update(Crud::PAGE_DETAIL, Action::INDEX, static function (Action $action) {
                return $action->setIcon('fa fa-list');
            })
            ->reorder(Crud::PAGE_DETAIL,[
                Action::INDEX,Action::DELETE
            ]);;
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Commerce');
        yield MenuItem::subMenu('Catalog','fas fa-tag')
            ->setSubItems([
                MenuItem::linkToCrud('All', 'fas fa-eye', Product::class),
                MenuItem::linkToCrud('Add', 'fas fa-plus', Product::class)
                    ->setAction(Crud::PAGE_NEW),
            ]);
        yield MenuItem::subMenu('Categories', 'fas fa-list')
            ->setSubItems([
                MenuItem::linkToCrud('All', 'fas fa-eye', Category::class),
                MenuItem::linkToCrud('Add', 'fas fa-plus', Category::class)
                    ->setAction(Crud::PAGE_NEW),
            ]);
        yield MenuItem::submenu('Orders','fas fa-shopping-cart')
            ->setSubItems([
                MenuItem::linkToCrud('All', 'fas fa-eye', Order::class)
                    ->setController(OrderCrudController::class),
                MenuItem::linkToCrud('Pending', 'far fa-question-circle', Order::class)
                    ->setController(OrderPendingCrudController::class),
                MenuItem::linkToCrud('Details', 'fas fa-info',OrderDetails::class)
            ]);
        yield MenuItem::submenu('Carriers','fas fa-truck')
            ->setSubItems([
                MenuItem::linkToCrud('All', 'fas fa-eye', Carrier::class),
                MenuItem::linkToCrud('Add', 'fas fa-plus', Carrier::class)
                    ->setAction(Crud::PAGE_NEW),
            ]);
        yield MenuItem::section('Design');
        yield MenuItem::linkToCrud('Header', 'fa fa-images', Header::class);

        yield MenuItem::section('Configuration');
        yield MenuItem::linkToCrud('Users', 'fas fa-user', User::class);

        yield MenuItem::section('Resources');
        yield MenuItem::linkToUrl('Symfony', 'fab fa-symfony', 'https://symfony.com')
            ->setLinkTarget('_blank');
    }

}