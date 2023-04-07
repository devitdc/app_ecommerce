<?php

namespace App\Controller\Admin;

use App\Entity\Header;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class HeaderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Header::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setPageTitle(Crud::PAGE_DETAIL, static function(Header $header) {
                return $header->getName();
            });
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->reorder(Crud::PAGE_DETAIL,[
                Action::INDEX,Action::EDIT,Action::DELETE
            ]);;
    }


    public function configureFields(string $pageName): iterable
    {
        if ($pageName === Crud::PAGE_DETAIL || $pageName === Crud::PAGE_INDEX) {
            $content = TextareaField::new('content')
                ->hideOnIndex()
                ->renderAsHtml();
        }elseif ($pageName === Crud::PAGE_EDIT || $pageName === Crud::PAGE_NEW) {
            $content = TextEditorField::new('content')
                ->hideOnIndex();
        }

        $fields = [
            TextField::new('name'),
            TextField::new('title'),
            $content,
            TextField::new('btnTitle'),
            TextField::new('btnURL')
                ->hideOnIndex(),
            ImageField::new('image')
                ->setUploadDir('public/images/uploads')
                ->setBasePath('images/uploads')
                ->setFormTypeOptions(['required' => false])
                ->setUploadedFileNamePattern('[randomhash].[extension]'),
            BooleanField::new('isActive', 'Active')
                /*->renderAsSwitch(false)*/
        ];

        return $fields;
    }

}
