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
                'attr' => [
                    'placeholder' => 'Start Date',
                ],
                'required' => false,
                'data' => $options['data']['startDate'],
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'attr' => [
                    'placeholder' => 'End Date',
                ],
                'required' => false,
                'data' => $options['data']['endDate'],
            ])
            ->add('submitDateRange', SubmitType::class, [
                'label' => 'Submit',
            ])
            ->add('submitDay', SubmitType::class, [
                'label' => 'Day',
            ])
            ->add('submitMonth', SubmitType::class, [
                'label' => 'Month',
            ])
            ->add('submitYear', SubmitType::class, [
                'label' => 'Year',
            ])
            ->add('submitDayBefore', SubmitType::class, [
                'label' => 'Day-1',
            ])
            ->add('submitMonthBefore', SubmitType::class, [
                'label' => 'Month-1',
            ])
            ->add('submitYearBefore', SubmitType::class, [
                'label' => 'Year-1',
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
