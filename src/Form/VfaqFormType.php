<?php
// src/Form/VfaqFormType.php
namespace App\Form;

use App\Entity\Vfaq;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class VfaqFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('question', TextType::class, [
                'label'       => 'Question',
                'constraints' => [
                    new NotBlank(['message' => 'Question cannot be blank']),
                    new Length(['max' => 500, 'maxMessage' => 'Question cannot exceed 500 characters']),
                ],
                'attr' => [
                    'placeholder' => 'Enter the FAQ question…',
                    'class'       => 'form-control',
                ],
            ])
            ->add('answer', TextareaType::class, [
                'label'       => 'Answer',
                'constraints' => [
                    new NotBlank(['message' => 'Answer cannot be blank']),
                ],
                'attr' => [
                    'placeholder' => 'Enter the answer…',
                    'class'       => 'form-control',
                    'rows'        => 5,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vfaq::class,
        ]);
    }
}
