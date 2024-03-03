<?php

namespace App\Form\Product;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'Nom',
            ])
            ->add('slug', TextType::class, [
                'required' => true,
                'label' => 'Slug',
            ])
            ->add('ref', TextType::class, [
                'required' => true,
                'label' => 'Référence',
            ])
            ->add('price', TextType::class, [
                'required' => true,
                'label' => 'Prix',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
