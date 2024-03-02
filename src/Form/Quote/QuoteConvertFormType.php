<?php

namespace App\Form\Quote;


use App\Enum\QuoteConvertEnum;
use App\Enum\PaymentMethodEnum;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuoteConvertFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('convertType', EnumType::class, [
                'class' => QuoteConvertEnum::class,
                'label' => 'Type de conversion',
                'required' => true,
                'expanded' => true,
            ])
            ->add('dueDate', DateType::class, [
                'label' => 'Date de l`\échéance',
                'required' => true,
                'widget' => 'single_text',
                'attr' => [
                    'min' => (new \DateTime())->format('Y-m-d'),
                ],
            ])
            ->add('paymentMethod', EnumType::class, [
                'class' => PaymentMethodEnum::class,
                'label' => 'Moyen de paiement',
                'required' => false,
                'expanded' => true,
            ])
            ->add('amount', NumberType::class, [
                'required' => false,
                'label' => 'Montant',
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
