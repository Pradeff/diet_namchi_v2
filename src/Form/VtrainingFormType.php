<?php

namespace App\Form;

use App\Entity\Vtraining;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VtrainingFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Title',
                'attr'  => [
                    'class'       => 'form-control',
                    'placeholder' => 'Enter training title',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr'  => [
                    'class'       => 'form-control',
                    'placeholder' => 'Enter training description',
                    'rows'        => 5,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vtraining::class,
        ]);
    }
}
