<?php

namespace App\Form\Invoice;

use App\Entity\Product;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceProductFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $products = $options['products'];

        $builder
            ->add('products', ChoiceType::class,
                [
                    'choices' => $products,
                    'placeholder' => '-------',
                    'choice_label' => function (Product $product) {
                        return $product->getName();
                    },
                    'choice_value' => function (Product $product = null) {
                        return $product ? $product->getId() : '';
                    },
                    'label' => 'Products',
                    'required' => true,
                    'multiple' => true,
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'products' => [],
        ]);
    }

}
