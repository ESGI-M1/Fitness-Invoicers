<?php

namespace App\Form\Item;

use App\Entity\Item;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ItemStepTwoFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', HiddenType::class, [
                'required' => true,
            ])
            ->add('quantity', NumberType::class, [
                'required' => true,
                'label' => false,
            ])
            ->add('taxes', NumberType::class, [
                'required' => true,
                'label' => false,
                'html5' => true,
            ])
            ->add('discountAmountOnItem', NumberType::class, [
                'required' => false,
                'label' => false,
                'html5' => true,
            ])
            ->add('discountAmountOnTotal', NumberType::class, [
                'required' => false,
                'label' => false,
                'html5' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Item::class,
        ]);
    }
}
