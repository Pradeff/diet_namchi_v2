<?php

namespace App\Form;

use App\Entity\Vcourse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class VcourseFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Course Title',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter course title...'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Title cannot be empty']),
                    new Length(['min' => 3, 'max' => 255])
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 6,
                    'placeholder' => 'Enter course description...'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Description cannot be empty']),
                    new Length(['min' => 10])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vcourse::class,
        ]);
    }
}
