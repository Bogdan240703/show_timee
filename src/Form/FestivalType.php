<?php

namespace App\Form;

use App\Entity\Band;
use App\Entity\Festival;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class FestivalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Name nu poate fi gol.']),
                    new Assert\Length(['min' => 2, 'max' => 255, 'minMessage' => 'Minim {{ limit }} caractere.',
                        'maxMessage' => 'Maxim {{ limit }} caractere.',]),
                ],
            ])
            ->add('location', TextType::class, ['constraints' => [new Assert\NotBlank(['message' => 'Location nu poate fi gol.']), new Assert\Length([
                'min' => 2,
                'max' => 255,
                'minMessage' => 'Locatia trebuie sa aiba cel putin {{ limit }} caractere.',
                'maxMessage' => 'Locatia nu poate avea mai mult de {{ limit }} caractere.',
            ]),]])
            ->add('price', IntegerType::class, [
                'constraints' => [
                    new Assert\NotNull(['message' => 'Pretul este obligatoriu.']),
                    new Assert\Positive(['message' => 'Pretul trebuie sa fie un numar pozitiv.']),
                ],
            ])
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotNull(['message' => 'Data de inceput este obligatorie.']),
                    new Assert\GreaterThanOrEqual([
                        'value' => (new \DateTime())->format('d-m-Y'),
                        'message' => 'Festivalul nu poate începe in trecut.',
                    ]),
                ]
            ])
            ->add('endDate', DateType::class, [
                'constraints' => [
                    new Assert\NotNull(['message' => 'Data de sfarsit este obligatorie.']),
                    new Assert\GreaterThanOrEqual([
                        'value' => (new \DateTime())->format('d-m-Y'),
                        'message' => 'Festivalul nu se poate termina in trecut',
                    ]),
                    new Assert\GreaterThan([
                        'propertyPath' => 'parent.all[startDate].data',
                        'message' => 'Data de final trebuie sa fie după data de inceput.',
                    ]),
                ]
            ])
            ->add('bands', EntityType::class, [
                'class' => Band::class,
                'choice_label' => 'name',
                'multiple' => true,
                'constraints' => [
                    new Assert\Count([
                        'min' => 1,
                        'minMessage' => 'Trebuie sa selectezi cel putin o trupa.',
                    ]),
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Festival::class,
        ]);
    }
}
