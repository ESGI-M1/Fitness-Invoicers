<?php

namespace App\Form\Quote;

use App\Entity\Company;
use App\Enum\InvoiceStatusEnum;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuoteSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $status = [];
        foreach (InvoiceStatusEnum::cases() as $statusEnum) {
            $status[$statusEnum->name] = $statusEnum;
        }

        $builder
            ->add('id', IntegerType::class, [
                'required' => false,
                'label' => 'N° de devis',
            ])
            ->add('discountAmount', NumberType::class, [
                'required' => false,
                'label' => 'Montant de la remise',
            ])
            ->add('discountPercent', NumberType::class, [
                'required' => false,
                'label' => 'Réduction %',
            ])
            ->add('status', ChoiceType::class, [
                'choices' => $status,
                'label' => 'Statut',
                'placeholder' => 'Tous',
                'choice_label' => function ($status) {
                    return $status->name;
                },
                'attr'=> [
                    'class' => 'select2'
                ]
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
    }
}
