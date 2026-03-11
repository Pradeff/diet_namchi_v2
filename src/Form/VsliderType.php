<?php

namespace App\Form;

use App\Entity\Vslider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class VsliderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Slide Title',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter slide title'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a title',
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Enter slide description'
                ],
            ])
            ->add('mediaType', ChoiceType::class, [
                'label' => 'Media Type',
                'choices' => [
                    'Image' => 'image',
                    'Video' => 'video',
                ],
                'attr' => [
                    'class' => 'form-select'
                ],
            ])
            ->add('mediaFile', FileType::class, [
                'label' => 'Media File (Image/Video)',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control dropify',
                    'accept' => 'image/*,video/*'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '20M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/jpg',
                            'image/gif',
                            'image/webp',
                            'video/mp4',
                            'video/webm',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image or video file',
                    ])
                ],
            ])
            ->add('sortOrder', IntegerType::class, [
                'label' => 'Sort Order',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0
                ],
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Active',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vslider::class,
        ]);
    }
}
