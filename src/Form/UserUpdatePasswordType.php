<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserUpdatePasswordType extends AbstractType
{
    private $entityMetaData;

    public function __construct(ValidatorInterface $validator)
    {
        $this->entityMetaData = $validator->getMetadataFor(User::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $constraints = [];
        foreach ($this->entityMetaData->getPropertyMetadata('password') as $set) {
            foreach ($set->getConstraints() as $constraint) {
                $constraints[] = $constraint;
            }
        }

        $builder
            ->add('old_password', PasswordType::class, [
                'required' => true,
                'mapped' => false,
                'invalid_message' => "Votre mot de passe est incorrect.",
                'attr' => [
                    'autofocus' => true
                ]
            ])
            ->add('new_password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'required' => true,
                'mapped' => false,
                'constraints' => $constraints
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'validation_groups' => ['update_password'],
            'allow_extra_fields' => true
        ]);
    }
}
