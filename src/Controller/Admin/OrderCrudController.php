<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;

class OrderCrudController extends AbstractCrudController
{
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(AdminUrlGenerator $adminUrlGenerator)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $preparation = Action::new('inProgressPrepared','In progress prepared', 'fas fa-box-open')
            ->linkToCrudAction('preparation')
            ->displayIf(static function(Order $order){
                return $order->getDeliveryState() === 3;
            });
        $delivery = Action::new('outForDelivery','Out for delivery', 'fas fa-truck')
            ->linkToCrudAction('delivery')
            ->displayIf(static function(Order $order){
                return $order->getDeliveryState() === 4;
            });
        $filterOrder = Action::new('productList', 'Product list', 'fas fa-tag')
            ->linkToCrudAction('filterToOrderDetails');

        return $actions
            ->add(Crud::PAGE_INDEX, $preparation)
            ->add(Crud::PAGE_INDEX, $delivery)
            ->add(Crud::PAGE_DETAIL, $preparation)
            ->add(Crud::PAGE_DETAIL, $delivery)
            ->add(Crud::PAGE_EDIT, $preparation)
            ->add(Crud::PAGE_EDIT, $delivery)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            /*->remove(Crud::PAGE_DETAIL, Action::EDIT)*/
            ->reorder(Crud::PAGE_INDEX, ['inProgressPrepared', 'outForDelivery',Action::DETAIL])
            ->reorder(Crud::PAGE_DETAIL, ['inProgressPrepared', 'outForDelivery'])
            ->reorder(Crud::PAGE_EDIT, ['inProgressPrepared', 'outForDelivery'])
            ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['id' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->setDisabled(),
            DateTimeField::new('createdAt', 'Date')
                ->setFormat('dd-MM-yyyy - HH:mm:ss')
                ->setTimezone('Europe/Paris')
                ->setDisabled(true),
            DateTimeField::new('updatedAt')
                ->setFormat('dd-MM-yyyy - HH:mm:ss')
                ->setTimezone('Europe/Paris')
                ->onlyOnDetail(),
            TextField::new('reference')
                ->setDisabled(),
            TextField::new('carrier_name')
                ->hideOnIndex()
                ->setDisabled(),
            Field::new('carrier_price')
                ->hideOnIndex()
                ->setDisabled(),
            /*MoneyField::new('TotalPrice')->setCurrency('EUR')->setCustomOption('storedAsCents', false),*/
            TextField::new('TotalOrder')
                ->setDisabled(),
            BooleanField::new('isPaid')
                ->setDisabled()
                ->renderAsSwitch(false),
            ChoiceField::new('deliveryState', 'Status')
                ->setChoices([
                    "Non payé" => 0,
                    "Panier abandonné" => 1,
                    "Paiement annulé" => 2,
                    "Commande validée" => 3,
                    "Préparation en cours" => 4,
                    "Livraison en cours" => 5,
                    "Livré" => 6
                ])
                ->escapeHtml(false)
                ->renderAsBadges([
                    0 => 'secondary',
                    1 => 'danger',
                    2 => 'danger',
                    3 => 'primary',
                    4 => 'warning',
                    5 => 'warning',
                    6 => 'success'
                ])
                ->setFormTypeOptions([
                    'by_reference' => false
                ]),
            TextField::new('FullDeliveryCustomer', 'Customer'),
            TelephoneField::new('deliveryPhone', 'Phone'),
            TextField::new('FullDeliveryAddress', 'Delivery Address')
                ->hideOnIndex()
                ->renderAsHtml()
                ->setDisabled(),
            ArrayField::new('orderDetails', 'Produits achetés')
                ->onlyOnDetail()
        ];

        /*return [
            IdField::new('id'),
            DateTimeField::new('createdAt')->setFormat('dd-MM-yyyy - HH:mm:ss')->setTimezone('Europe/Paris'),
            TextField::new('carrier_name'),
            TextField::new('TotalOrder'),
            BooleanField::new('isPaid'),
            TextField::new('FullDeliveryCustomer', 'Customer'),
            TextField::new('deliveryPhone', 'Phone'),
            TextField::new('FullDeliveryAddress', 'Delivery Address')->renderAsHtml(),
        ];*/
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('createdAt')
            ->add('reference')
            ->add('deliveryFirstname')
            ->add('deliveryLastname')
            ->add(BooleanFilter::new('isPaid')
                ->setFormTypeOption('expanded', false))
            ;
    }

    public function preparation(AdminContext $adminContext, EntityManagerInterface $entityManager): RedirectResponse
    {
        $order = $adminContext->getEntity()->getInstance();

        if ($order instanceof Order && $order->getDeliveryState() !== 4) {
            $order->setDeliveryState(4);
            $entityManager->flush();

            $this->addFlash('info', "La commande <strong>". $order->getReference() ."</strong> est en cours de préparation.");
        }

        if (strpos($adminContext->getReferrer(), 'crudAction=index')) {
            $url = $this->adminUrlGenerator->setController(self::class)
                ->setAction(Action::INDEX)
                ->generateUrl();
        }
        else {
            $url = $this->adminUrlGenerator->setController(self::class)
                ->setAction(Action::DETAIL)
                ->generateUrl();
        }

        return $this->redirect($url);
    }

    public function delivery(AdminContext $adminContext, EntityManagerInterface $entityManager): RedirectResponse
    {
        $order = $adminContext->getEntity()->getInstance();
        if ($order instanceof Order && $order->getDeliveryState() !== 5) {
            $order->setDeliveryState(5);
            $entityManager->flush();

            $this->addFlash('info', "La livraison de la commande <strong>". $order->getReference() ."</strong> est en cours.");
        }

        if (strpos($adminContext->getReferrer(), 'crudAction=index')) {
            $url = $this->adminUrlGenerator->setController(self::class)
                ->setAction(Action::INDEX)
                ->generateUrl();
        }
        else {
            $url = $this->adminUrlGenerator->setController(self::class)
                ->setAction(Action::DETAIL)
                ->generateUrl();
        }

        return $this->redirect($url);
    }

    public function filterToOrderDetails(AdminContext $adminContext): RedirectResponse
    {
        foreach ($adminContext->getEntity()->getInstance()->getOrderDetails() as $detail) {
            $orderDetailID = $detail->getId();
        }

        $url = $this->adminUrlGenerator
            ->removeReferrer()
            ->setController(OrderDetailsCrudController::class)
            ->setAction(Crud::PAGE_INDEX)
            ->unset('entityId')
            ->set("filters[MyOrder][value]", $adminContext->getEntity()->getInstance()->getId())
            ->set('filters[MyOrder][comparison]', ["is same"])
            ->generateUrl();

        return $this->redirect($url);
    }

}
