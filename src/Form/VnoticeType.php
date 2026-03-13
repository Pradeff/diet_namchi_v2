<?php

namespace App\Form;

use App\Entity\Vnotice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class VnoticeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ── REQUIRED ──────────────────────────────────────────────────────
            // NO inline constraints here — NotBlank, Length, NotNull are already
            // defined on the entity, so removing them from the form prevents the
            // error messages from appearing twice.
            ->add('title', TextType::class, [
                'label'      => 'Notice Title <span class="text-danger">*</span>',
                'label_html' => true,
                'attr'       => [
                    'class'       => 'form-control',
                    'placeholder' => 'Enter notice title',
                ],
            ])
            ->add('noticeDate', DateType::class, [
                'label'      => 'Notice Date <span class="text-danger">*</span>',
                'label_html' => true,
                'widget'     => 'single_text',
                'html5'      => true,
                'required'   => true,
                'attr'       => [
                    'class'        => 'form-control',
                    'autocomplete' => 'off',
                ],
            ])

            // ── OPTIONAL ──────────────────────────────────────────────────────
            ->add('description', TextareaType::class, [
                'label'    => 'Description',
                'required' => false,
                'attr'     => [
                    'class'       => 'form-control',
                    'rows'        => 4,
                    'placeholder' => 'Short description of the notice (optional)',
                ],
            ])
            ->add('pdfFile', FileType::class, [
                'label'    => 'PDF File',
                'mapped'   => false,
                'required' => false,
                // File constraint stays here because pdfFile is unmapped —
                // the entity cannot validate it, so this one is NOT a duplicate.
                'constraints' => [
                    new File([
                        'mimeTypes'        => ['application/pdf'],
                        'mimeTypesMessage' => 'Please upload a valid PDF file.',
                    ]),
                ],
                'attr' => [
                    'class'  => 'form-control',
                    'accept' => '.pdf',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vnotice::class,
        ]);
    }
}
