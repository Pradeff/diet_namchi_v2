<?php

namespace App\Form;

use App\Entity\Vabout;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class VaboutFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Page Title',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter the about page title...',
                    'maxlength' => 255
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Title is required'
                    ]),
                    new Length([
                        'min' => 3,
                        'max' => 255,
                        'minMessage' => 'Title must be at least {{ limit }} characters long',
                        'maxMessage' => 'Title cannot be longer than {{ limit }} characters'
                    ])
                ]
            ])
            ->add('subtitle', TextType::class, [
                'label' => 'Subtitle',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter a subtitle (optional)...',
                    'maxlength' => 255
                ],
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Subtitle cannot be longer than {{ limit }} characters'
                    ])
                ]
            ])
            ->add('description1', TextareaType::class, [
                'label' => 'First Description',
                'required' => false,
                'attr' => [
                    'class' => 'form-control summernote',
                    'rows' => 6,
                    'placeholder' => 'Enter the first part of your about content...'
                ],
                'help' => ''
            ])
            ->add('description2', TextareaType::class, [
                'label' => 'Mission',
                'required' => false,
                'attr' => [
                    'class' => 'form-control summernote',
                    'rows' => 6,
                    'placeholder' => ''
                ],
                'help' => ''
            ])
            ->add('description3', TextareaType::class, [
                'label' => 'Vision',
                'required' => false,
                'attr' => [
                    'class' => 'form-control summernote',
                    'rows' => 6,
                    'placeholder' => ''
                ],
                'help' => ''
            ])
            ->add('cover_image', FileType::class, [
                'label' => false,
                'mapped' => false, // Tell that there is no Entity to link
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/jpg',
                            'image/webp',
                            'image/gif'
                        ],
                        'mimeTypesMessage' => "This file isn't valid. Upload only image file.",
                        'maxSizeMessage' => "Upload below 1MB file.",
                    ])
                ],
            ])
            ->add('video', TextType::class, [
                'label' => 'Video URL',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter YouTube or Vimeo URL (optional)...',
                    'maxlength' => 255
                ],
                'help' => 'Add a YouTube or Vimeo video URL to embed in your about page.',
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Video URL cannot be longer than {{ limit }} characters'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vabout::class,
        ]);
    }
}
