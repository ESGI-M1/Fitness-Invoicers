<?php

namespace App\Form\Product;

use App\Entity\Category;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('alias', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('price', IntegerType::class, [
                'label' => 'Prix',
            ])
            ->add('category', EntityType::class,
                [
                    'class' => Category::class,
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->createQueryBuilder('c');
                    },
                    'placeholder' => 'Toutes',
                    'choice_label' => 'name',
                    'label' => 'Catégorie',
                    'attr'=>[
                        'class' => 'select2'
                    ],
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
    }
}
