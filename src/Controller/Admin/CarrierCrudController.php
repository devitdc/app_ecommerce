<?php

namespace App\Controller\Admin;

use App\Entity\Carrier;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CarrierCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Carrier::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setPageTitle(Crud::PAGE_DETAIL, static function(Carrier $carrier) {
                return $carrier->getName();
            });
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->reorder(Crud::PAGE_DETAIL,[
                Action::INDEX,Action::EDIT,Action::DELETE
            ]);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name')
            ->hideOnDetail(),
            TextField::new('delay'),
            MoneyField::new('price')
                ->setCurrency('EUR')
                ->setStoredAsCents(false),
            TextField::new('Type')
        ];
    }

}
