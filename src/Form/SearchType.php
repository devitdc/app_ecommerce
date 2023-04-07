<?php

namespace App\Form;

use App\Class\Search;
use App\Entity\Category;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('string', TextType::class, [
                'label' => 'Par mots clés',
                'required' => false,
                'attr' => [
                    'class' => 'mb-2 form-control'
                ]
            ])
            ->add('categories', EntityType::class, [
                'class' => Category::class,
                'label' => 'Par catégories',
                'attr' => [
                    'class' => 'mb-2 form-control-sm product-select-category-index',
                    'placeholder' => 'Sélectionner une catégorie...'
                ],
                'required' => false,
                'multiple' => true,
                'expanded' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Rechercher',
                'attr' => [
                    'class' => 'btn btn-outline-primary mt-3',
                ],
            ])
        ;
    }
    
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Search::class,
            'method' => 'GET',
            'csrf_protection' => false
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }

}