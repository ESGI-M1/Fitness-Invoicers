<?php

namespace App\Form\Payment;

use App\Entity\Payment;
use App\Enum\PaymentMethodEnum;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('method', EnumType::class, [
                'label' => 'Méthode de paiement',
                'class' => PaymentMethodEnum::class,
                'required' => true,
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('amount', NumberType::class, [
                'label' => 'Montant',
                'required' => true,
                'html5' => true,
            ]);

    }


    function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Payment::class,
        ]);
    }
}
