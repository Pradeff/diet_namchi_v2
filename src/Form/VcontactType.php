<?php

namespace App\Form;

use App\Entity\Vcontact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Url;

class VcontactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Phone Numbers - All Optional
            ->add('phone1', TelType::class, [
                'label' => 'Phone 1',
                'required' => false,
                'attr' => [
                    'placeholder' => '+91 98765 43210',
                ],
            ])
            ->add('phone2', TelType::class, [
                'label' => 'Phone 2 (WhatsApp)',
                'required' => false,
                'attr' => [
                    'placeholder' => '+91 98765 43211',
                ],
            ])
            ->add('phone3', TelType::class, [
                'label' => 'Phone 3',
                'required' => false,
                'attr' => [
                    'placeholder' => '+91 98765 43212',
                ],
            ])
            ->add('phone4', TelType::class, [
                'label' => 'Phone 4',
                'required' => false,
                'attr' => [
                    'placeholder' => '+91 98765 43213',
                ],
            ])
            ->add('phone5', TelType::class, [
                'label' => 'Phone 5',
                'required' => false,
                'attr' => [
                    'placeholder' => '+91 98765 43214',
                ],
            ])

            // Email Addresses - All Optional with Email Validation
            ->add('email1', EmailType::class, [
                'label' => 'Primary Email',
                'required' => false,
                'constraints' => [
                    new Email([
                        'message' => 'Please enter a valid email address',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'contact@example.com',
                ],
            ])
            ->add('email2', EmailType::class, [
                'label' => 'Email 2',
                'required' => false,
                'constraints' => [
                    new Email([
                        'message' => 'Please enter a valid email address',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'support@example.com',
                ],
            ])
            ->add('email3', EmailType::class, [
                'label' => 'Email 3',
                'required' => false,
                'constraints' => [
                    new Email([
                        'message' => 'Please enter a valid email address',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'info@example.com',
                ],
            ])
            ->add('email4', EmailType::class, [
                'label' => 'Email 4',
                'required' => false,
                'constraints' => [
                    new Email([
                        'message' => 'Please enter a valid email address',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'sales@example.com',
                ],
            ])
            ->add('email5', EmailType::class, [
                'label' => 'Email 5',
                'required' => false,
                'constraints' => [
                    new Email([
                        'message' => 'Please enter a valid email address',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'admin@example.com',
                ],
            ])

            // Address - Optional
            ->add('address', TextareaType::class, [
                'label' => 'Full Address',
                'required' => false,
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Enter your complete address including street, city, state, and postal code',
                ],
            ])
            ->add('map', TextareaType::class, [
                'label' => 'Google Map Embed Code',
                'required' => false,
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Paste your Google Maps embed code here',
                ],
            ])

            // Social Links - All Optional with URL Validation
            ->add('fb', UrlType::class, [
                'label' => 'Facebook URL',
                'required' => false,
                'constraints' => [
                    new Url([
                        'message' => 'Please enter a valid URL',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'https://facebook.com/yourpage',
                ],
            ])
            ->add('insta', UrlType::class, [
                'label' => 'Instagram URL',
                'required' => false,
                'constraints' => [
                    new Url([
                        'message' => 'Please enter a valid URL',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'https://instagram.com/youraccount',
                ],
            ])
            ->add('tripadv', UrlType::class, [
                'label' => 'TripAdvisor URL',
                'required' => false,
                'constraints' => [
                    new Url([
                        'message' => 'Please enter a valid URL',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'https://tripadvisor.com/your-listing',
                ],
            ])
            ->add('tw', UrlType::class, [
                'label' => 'Twitter URL',
                'required' => false,
                'constraints' => [
                    new Url([
                        'message' => 'Please enter a valid URL',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'https://twitter.com/youraccount',
                ],
            ])
            ->add('yt', UrlType::class, [
                'label' => 'YouTube URL',
                'required' => false,
                'constraints' => [
                    new Url([
                        'message' => 'Please enter a valid URL',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'https://youtube.com/yourchannel',
                ],
            ])
            ->add('linkedin', UrlType::class, [
                'label' => 'LinkedIn URL',
                'required' => false,
                'constraints' => [
                    new Url([
                        'message' => 'Please enter a valid URL',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'https://linkedin.com/in/yourprofile',
                ],
            ])
            ->add('telegram', UrlType::class, [
                'label' => 'Telegram URL',
                'required' => false,
                'constraints' => [
                    new Url([
                        'message' => 'Please enter a valid URL',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'https://t.me/youraccount',
                ],
            ])

            // Site Settings - All Optional
            ->add('sitetitle', TextType::class, [
                'label' => 'Site Title',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Your Website Title',
                ],
            ])
            ->add('sitelink', UrlType::class, [
                'label' => 'Site Link',
                'required' => false,
                'constraints' => [
                    new Url([
                        'message' => 'Please enter a valid URL',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'https://yourwebsite.com',
                ],
            ])
            ->add('sitekeyword', TextareaType::class, [
                'label' => 'Site Keywords',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'keyword1, keyword2, keyword3, etc.',
                ],
            ])
            ->add('sitedescription', TextareaType::class, [
                'label' => 'Site Description',
                'required' => false,
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Brief description of your website for search engines',
                ],
            ])


            // File Uploads - All Optional
            ->add('favicon', FileType::class, [
                'label' => 'Favicon',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/png',
                            'image/gif',
                            'image/svg+xml',
                            'image/webp',
                            'image/x-icon',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image file (JPG, PNG, GIF, SVG, WEBP, ICO)',
                        'maxSizeMessage' => 'The file is too large ({{ size }} {{ suffix }}). Maximum allowed size is {{ limit }} {{ suffix }}',
                    ]),
                ],
            ])
            ->add('logo1', FileType::class, [
                'label' => 'Logo 1 (Light Square)',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/png',
                            'image/gif',
                            'image/svg+xml',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image file',
                        'maxSizeMessage' => 'The file is too large. Maximum allowed size is 1MB',
                    ]),
                ],
            ])
            ->add('logo2', FileType::class, [
                'label' => 'Logo 2 (Dark Square)',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/png',
                            'image/gif',
                            'image/svg+xml',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image file',
                        'maxSizeMessage' => 'The file is too large. Maximum allowed size is 1MB',
                    ]),
                ],
            ])
            ->add('logo3', FileType::class, [
                'label' => 'Logo 3 (Light Horizontal)',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/png',
                            'image/gif',
                            'image/svg+xml',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image file',
                        'maxSizeMessage' => 'The file is too large. Maximum allowed size is 1MB',
                    ]),
                ],
            ])
            ->add('logo4', FileType::class, [
                'label' => 'Logo 4 (Dark Horizontal)',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/png',
                            'image/gif',
                            'image/svg+xml',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image file',
                        'maxSizeMessage' => 'The file is too large. Maximum allowed size is 1MB',
                    ]),
                ],
            ])
            ->add('opentime', TextType::class, [
                'label' => 'Open Time',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Mon - Sat: 10:00 AM - 5:00 PM',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vcontact::class,
        ]);
    }
}
