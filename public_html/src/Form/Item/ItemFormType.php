<?php

namespace App\Form\Item;

use App\Entity\Item;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ItemFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('productLabel', TextType::class, [
                'required' => true,
                'label' => 'Label',
            ])
            ->add('productPrice', TextType::class, [
                'required' => true,
                'label' => 'Prix',
            ])
            ->add('productRef', TextType::class, [
                'required' => true,
                'label' => 'Référence',
            ])
            ->add('productLabel', TextType::class, [
                'required' => true,
                'label' => 'Label',
            ])
            ->add('quantity', IntegerType::class, [
                'required' => true,
                'label' => 'Quantité',
            ])
            ->add('taxes', TextType::class, [
                'required' => true,
                'label' => 'Taxes',
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
