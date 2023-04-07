<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Service\CsvExporter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FilterFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use FM\ElfinderBundle\Form\Type\ElFinderType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ProductCrudController extends AbstractCrudController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AdminUrlGenerator $adminUrlGenerator
    )
    {
    }


    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $showOnWebsite = Action::new('view')
            ->linkToUrl(function (Product $product){
                return $this->generateUrl('product_show', [
                    'slug' => $product->getSlug()
                ]);
            })
            ->setIcon('fas fa-globe')
            ->setLabel('View on website')
            ->setHtmlAttributes([
                'target' => '_blank'
            ]);

        $duplicate = Action::new('duplicate')
            ->linkToCrudAction('duplicateProduct')
            ->setIcon('fas fa-copy');

        return $actions
            ->add(Crud::PAGE_INDEX, $showOnWebsite)
            ->add(Crud::PAGE_INDEX, $duplicate)
            ->add(Crud::PAGE_DETAIL, $showOnWebsite)
            ->add(Crud::PAGE_DETAIL, $duplicate)
            ->add(Crud::PAGE_EDIT, Action::DELETE)
            ->reorder(Crud::PAGE_INDEX, [
                'view',Action::DETAIL,Action::EDIT,'duplicate',Action::DELETE
            ])
            ->reorder(Crud::PAGE_DETAIL, [
                'view',Action::INDEX,Action::EDIT,'duplicate',Action::DELETE
            ])
            ->reorder(Crud::PAGE_EDIT, [
                Action::INDEX,Action::SAVE_AND_CONTINUE,Action::SAVE_AND_RETURN,Action::DELETE
            ])
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        if ($pageName === Crud::PAGE_DETAIL || $pageName === Crud::PAGE_INDEX) {
            $description = TextareaField::new('description')
                ->hideOnIndex()
                ->renderAsHtml();
        }elseif ($pageName === Crud::PAGE_EDIT || $pageName === Crud::PAGE_NEW) {
            /*$description = TextEditorField::new('description')
                ->setFormType(CKEditorType::class)
                ->hideOnIndex();*/
            $description = TextEditorField::new('description')
                ->hideOnIndex();
        }

        $fields = [
            ImageField::new('image')
                ->setUploadDir('public/images/uploads')
                ->setBasePath('images/uploads')
                ->setFormTypeOptions([
                    'required' => false
                ])
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setSortable(false),
                /*->setFormTypeOptions([
                    "multiple" => true,
                ]),*/
            /*Field::new('image', 'Image')
                ->setFormType(ElFinderType::class)
                ->setFormTypeOptions([
                    'instance' => 'default',
                    'enable' => true,
                ]),*/
            TextField::new('name'),
            SlugField::new('slug')
                ->setTargetFieldName('name')
                ->hideOnIndex(),
            $description,
            IntegerField::new('stock'),
            MoneyField::new('price')
                ->setCurrency('EUR')
                ->setCustomOption('storedAsCents', false),
            AssociationField::new('category')
                ->setQueryBuilder(function (QueryBuilder $queryBuilder) {
                    return $queryBuilder->andWhere('entity.isActive = 1');
                }),
            BooleanField::new('isActive', 'Online'),
            BooleanField::new('isTopSeller', 'Top Seller')
        ];

        return $fields;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setPageTitle(Crud::PAGE_DETAIL,static function(Product $product) {
                return $product->getName();
            })
            ->addFormTheme('@FOSCKEditor/Form/ckeditor_widget.html.twig')
            ->addFormTheme('@FMElfinder/Form/elfinder_widget.html.twig');
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $entityInstance->setCreatedAt(new \DateTimeImmutable(null, new \DateTimeZone('Europe/Paris')));

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $entityInstance->setUpdatedAt(new \DateTimeImmutable(null, new \DateTimeZone('Europe/Paris')));

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function duplicateProduct(AdminContext $adminContext): RedirectResponse
    {
        /** @var Product $product */
        $product = $adminContext->getEntity()->getInstance();

        $duplicateProduct = clone $product;
        $duplicateProduct->setName($product->getName().'_clone')
            ->setIsActive(false)
            ->setIsTopSeller(false);

        //dd($duplicateProduct);

        parent::persistEntity($this->entityManager, $duplicateProduct);

        $url = $this->adminUrlGenerator->setController(ProductCrudController::class)
            ->setAction(Action::EDIT)
            ->setEntityId($duplicateProduct->getId())
            ->generateUrl();

        return $this->redirect($url);

    }

}
