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

        $attr = [
            'class' => 'w-10 p-2 bg-secondary shadow-md rounded-lg dark:text-white cursor-pointer',
        ];

        $builder
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'label' => false,
                'required' => false,
                'data' => $options['data']['startDate'],
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'label' => false,
                'required' => false,
                'data' => $options['data']['endDate'],
            ])
            ->add('submitDateRange', SubmitType::class, [
                'label' => 'Soumettre',
                'attr' => $attr
            ])
            ->add('submitDay', SubmitType::class, [
                'label' => 'Jour',
                'attr' => $attr
            ])
            ->add('submitMonth', SubmitType::class, [
                'label' => 'Mois',
                'attr' => $attr
            ])
            ->add('submitYear', SubmitType::class, [
                'label' => 'Année',
                'attr' => $attr
            ])
            ->add('submitDayBefore', SubmitType::class, [
                'label' => 'Jour-1',
                'attr' => $attr
            ])
            ->add('submitMonthBefore', SubmitType::class, [
                'label' => 'Mois-1',
                'attr' => $attr
            ])
            ->add('submitYearBefore', SubmitType::class, [
                'label' => 'Année-1',
                'attr' => $attr
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
