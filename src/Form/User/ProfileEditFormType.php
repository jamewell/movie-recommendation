<?php

namespace App\Form\User;

use App\Entity\Genre;
use App\Validator\Constraints\ImageConstraint;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ProfileEditFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'First Name',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Last Name',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('profilePicture', FileType::class, [
                'label' => 'Profile Picture',
                'required' => false,
                'mapped' => false,
                'data_class' => null,
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new ImageConstraint(),
                ],
            ])
            ->add('favoriteGenres', EntityType::class, [
                'class' => Genre::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'attr' => [
                    'class' => 'form-check-input',
                ],
            ])
        ;
    }
}
