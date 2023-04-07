<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Service\CsvExporter;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FilterFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class UserCrudController extends AbstractCrudController
{
    private SecurityController $securityController;

    public function __construct(SecurityController $securityController)
    {
        $this->securityController = $securityController;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud);
            /*->showEntityActionsInlined();*/
            /*->setFormOptions(['validation_groups' => ['Default']],['validation_groups' => ['update_infos']]);*/
    }


    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
            ->reorder(Crud::PAGE_DETAIL,[
                Action::INDEX,Action::EDIT,Action::DELETE
            ]);;
    }

    public function configureFields(string $pageName): iterable
    {
       /* if ($pageName === Crud::PAGE_EDIT) {
            $password = TextField::new('plainPassword')
                ->setFormType(RepeatedType::class)
                ->setFormTypeOptions([
                    'type' => PasswordType::class,
                    'invalid_message' => 'The password fields must match.',
                    'first_options' => [
                        'label' => 'Password',
                        'row_attr' => [
                            'class' => 'col-md-5',
                        ],
                    ],
                    'second_options' => [
                        'label' => 'Confirm Password',
                        'row_attr' => [
                            'class' => 'col-md-5',
                        ],
                    ],
                    'mapped' => true,
                ])
                ->onlyOnForms();
        }elseif ($pageName === Crud::PAGE_NEW) {
            $password = TextField::new('password')
                ->setFormType(RepeatedType::class)
                ->setFormTypeOptions([
                    'type' => PasswordType::class,
                    'invalid_message' => 'The password fields must match.',
                    'first_options' => [
                        'label' => 'Password',
                        'row_attr' => [
                            'class' => 'col-md-5',
                        ],
                    ],
                    'second_options' => [
                        'label' => 'Confirm Password',
                        'row_attr' => [
                            'class' => 'col-md-5',
                        ],
                    ],
                    'mapped' => true,
                ])
                ->onlyOnForms()
                ->setRequired(true);
        }else {
            $password = TextField::new('password')
                ->onlyOnForms()
                ->setRequired(false);
        }*/

        return [
            FormField::addPanel('User Details')
                ->collapsible(),
            IdField::new('id')
                ->hideOnForm(),
            TextField::new('email'),
            TextField::new('firstname'),
            TextField::new('lastname'),
            ChoiceField::new('roles')
                ->setChoices([
                    "Utilisateur" => "ROLE_USER",
                    "Administrateur"=> "ROLE_ADMIN"
                ])
                ->setFormTypeOptions([
                    'multiple' => true,
                    'expanded' => false,
                ]),
            FormField::addPanel('Change password')
                ->collapsible()
                ->onlyWhenUpdating(),
            FormField::addPanel('Password')
                ->collapsible()
                ->onlyWhenCreating(),
            TextField::new('plainPassword')
                ->setFormType(RepeatedType::class)
                ->setFormTypeOptions([
                    'type' => PasswordType::class,
                    'invalid_message' => 'The password fields must match.',
                    'first_options' => [
                        'label' => 'Password',
                        'row_attr' => [
                            'class' => 'col-md-5',
                        ],
                    ],
                    'second_options' => [
                        'label' => 'Confirm Password',
                        'row_attr' => [
                            'class' => 'col-md-5',
                        ],
                    ],
                    'mapped' => true,
                ])
                ->onlyOnForms()
                ->setRequired($pageName === Crud::PAGE_NEW)
        ];
    }

/*    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $entityInstance->setPassword($this->encodePassword($entityInstance->getPassword(), $this->getUser()));

        parent::persistEntity($entityManager, $entityInstance);
    }*/

    /*public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $entityInstance->setPassword($this->encodePassword($entityInstance->getPlainPassword(), $this->getUser()));

        parent::updateEntity($entityManager, $entityInstance);
    }*/


/*    public function encodePassword($password, $user): string
    {
        if (!$user instanceof User) {
            throw new \LogicException('Wrong user.');
        }
        else if (!$password) {
            throw new \LogicException("No password provided.");
        }

        return $this->securityController->passwordHasher($user,$password);
    }*/

}
