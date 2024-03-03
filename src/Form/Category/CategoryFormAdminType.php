<?php

namespace App\Form\Category;

use App\Entity\Category;
use App\Entity\Company;
use App\Entity\Product;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryFormAdminType extends AbstractType
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
            ->add('company', EntityType::class,
                [
                    'class' => Company::class,
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->createQueryBuilder('c');
                    },
                    'placeholder' => '----------',
                    'choice_label' => 'name',
                    'label' => 'Entreprise',
                    'required' => true
                ]
            )
//            ->add('products', EntityType::class,
//                [
//                    'class' => Product::class,
//                    'query_builder' => static function (EntityRepository $er) {
//                        return $er->createQueryBuilder('p');
//                    },
//                    'placeholder' => '----------',
//                    'choice_label' => 'name',
//                    'label' => 'Produit',
//                    'required' => false
//                ]
//            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Category::class,
                'csrf_protection' => false,
                'allow_extra_fields' => true,
                'attr' => [
                    'class' => 'add-form do-confirm',
                    'data-target' => '.modal-content',
                ],
                'update' => true,
            ]
        );
    }
}
