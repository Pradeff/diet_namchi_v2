<?php

namespace App\Form;

use App\Entity\Vteam;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class VteamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('designation')
            ->add('description', TextareaType::class, [
                'label' => 'Full Address',
                'required' => false,
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Enter your complete address including street, city, state, and postal code',
                ],
            ])
            ->add('phone', TextType::class, ['required' => false])
            ->add('email', EmailType::class, ['required' => false])
            ->add('image', FileType::class, [
                'label' => false,
                'mapped' => false, // Not mapped to Doctrine entity
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/jpg',
                            'image/gif',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => "This file isn't valid. Upload only image file.",
                        'maxSizeMessage' => "Upload below 1MB file.",
                    ])
                ],
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vteam::class,
        ]);
    }
}
