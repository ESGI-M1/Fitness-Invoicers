<?php

namespace App\Form\Invoice;

use App\Entity\Invoice;
use App\Enum\InvoiceStatusEnum;
use App\Enum\PaymentMethodEnum;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceStatusFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', EnumType::class, [
                'class' => InvoiceStatusEnum::class,
                'label' => 'Status',
                'required' => true,
            ])
            ->add('paymentMethod', EnumType::class, [
                'class' => PaymentMethodEnum::class,
                'label' => 'Moyen de paiement',
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'mapped' => false,
            ]);
    }


    function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Invoice::class,
        ]);
    }
}
