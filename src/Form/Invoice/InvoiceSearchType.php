<?php

namespace App\Form\Invoice;

use App\Entity\Company;
use App\Enum\InvoiceStatusEnum;
use App\Service\CompanySession;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceSearchType extends AbstractType
{
    public function __construct(CompanySession $companySession)
    {
        $this->companySession = $companySession;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $status = [];
        foreach (InvoiceStatusEnum::cases() as $statusEnum) {
            $status[$statusEnum->name] = $statusEnum;
        }
        $builder
            ->add('id', IntegerType::class, [
                'required' => false,
                'label' => 'N° de facture',
            ])
            ->add('discountTotal', NumberType::class, [
                'required' => false,
                'label' => 'Montant total',
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
