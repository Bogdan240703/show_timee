<?php

namespace App\Form;

use App\Entity\Band;
use App\Enum\MusicGenre;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\File;

class BandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Name nu poate fi gol.']),
                    new Assert\Length(['min' => 2, 'max' => 255, 'minMessage' => 'Minim {{ limit }} caractere.',
                        'maxMessage' => 'Maxim {{ limit }} caractere.', ]),
                ],
            ])
            ->add('musicGenre', EnumType::class, [
                'placeholder' => 'Choose a genre',
                'class' => MusicGenre::class,
                'constraints' => [
                    new Assert\NotNull([
                        'message' => 'Te rog să alegi un gen muzical.',
                    ]),
                ],
            ])
            ->add('picture', FileType::class, [
                'label' => 'Band picture (JPG/PNG file)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Please upload a valid image (JPG or PNG)',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Band::class,
        ]);
    }
}
