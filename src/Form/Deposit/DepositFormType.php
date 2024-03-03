<?php

namespace App\Form\Deposit;

use App\Entity\Deposit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DepositFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Deposit::class,
        ]);
    }
}
