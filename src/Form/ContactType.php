<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class,[
                'label' => false,
                'attr' => [
                    'placeholder' => 'Firstname',
                    'class' => 'bg-light'
                ]
            ])
            ->add('lastname', TextType::class,[
                'label' => false,
                'attr' => [
                    'placeholder' => 'Lastname',
                    'class' => 'bg-light'
                ]
            ])
            ->add('email', EmailType::class,[
                'label' => false,
                'attr' => [
                    'placeholder' => 'Email',
                    'class' => 'bg-light'
                ]
            ])
            ->add('subject', TextType::class,[
                'label' => false,
                'attr' => [
                    'placeholder' => 'Subject',
                    'class' => 'bg-light'
                ]
            ])
            ->add('message', TextareaType::class,[
                'label' => false,
                'attr' => [
                    'placeholder' => 'Message...',
                    'class' => 'bg-light',
                    'rows' => 5
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
