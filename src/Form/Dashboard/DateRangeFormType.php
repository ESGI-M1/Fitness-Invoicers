<?php

namespace App\Form\Dashboard;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class DateRangeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'du',
                'attr' => [
                    'placeholder' => 'Start Date',
                ],
                'required' => false,
                'data' => $options['data']['startDate'],
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'au',
                'attr' => [
                    'placeholder' => 'End Date',
                ],
                'required' => false,
                'data' => $options['data']['endDate'],
            ])
            ->add('submitDateRange', SubmitType::class, [
                'label' => 'Soumettre',
            ])
            ->add('submitDay', SubmitType::class, [
                'label' => 'Jour',
            ])
            ->add('submitMonth', SubmitType::class, [
                'label' => 'Mois',
            ])
            ->add('submitYear', SubmitType::class, [
                'label' => 'Année',
            ])
            ->add('submitDayBefore', SubmitType::class, [
                'label' => 'Jour-1',
            ])
            ->add('submitMonthBefore', SubmitType::class, [
                'label' => 'Mois-1',
            ])
            ->add('submitYearBefore', SubmitType::class, [
                'label' => 'Année-1',
            ]);
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data' => [
                'startDate' => new \DateTime('first day of this month'),
                'endDate' => new \DateTime('last day of this month'),
            ]
        ]);
    }

}
