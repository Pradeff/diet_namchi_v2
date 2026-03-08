<?php

namespace App\Form;

use App\Entity\Vgallery;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class VgalleryFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Gallery Title *',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter gallery title',
                    'required' => true
                ],
                'empty_data' => '',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Gallery title is required and cannot be empty',
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Gallery title must be at least {{ limit }} characters long',
                        'max' => 255,
                        'maxMessage' => 'Gallery title cannot be longer than {{ limit }} characters',
                    ]),
                    new Regex([
                        'pattern' => '/^[a-zA-Z0-9\s\-_.()]+$/',
                        'message' => 'Gallery title can only contain letters, numbers, spaces, hyphens, underscores, dots and parentheses'
                    ])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vgallery::class,
        ]);
    }
}
