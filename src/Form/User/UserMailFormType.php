<?php

namespace App\Form\User;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserMailFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('invoiceMailContent', TextAreaType::class, [
                'label' => 'Mail par défaut pour les factures',
                'required' => false,
            ])
            ->add('quoteMailContent', TextAreaType::class, [
                'label' => 'Mail par défaut pour les devis',
                'required' => false,
            ])
            ->add('mailSignature', TextAreaType::class, [
                'label' => 'Signature des mails',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => User::class,
            ]
        );
    }
}
