<?php

namespace App\Form\Quote;

use App\Entity\Deposit;
use App\Entity\Invoice;
use App\Entity\Quote;
use App\Enum\QuoteStatusEnum;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuoteFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('discountAmount', NumberType::class, [
                'required' => false,
                'label' => 'Montant de la remise',
            ])
            ->add('discountPercent', NumberType::class, [
                'required' => true,
                'label' => '%',
            ])
            ->add('deposits', EntityType::class,
                [
                    'class' => Deposit::class,
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->createQueryBuilder('d');
                    },
                    'placeholder' => '-------',
                    'choice_label' => 'price',
                    'label' => 'Rapports',
                    'required' => false,
                    'multiple' => true
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Quote::class,
        ]);
    }
}
