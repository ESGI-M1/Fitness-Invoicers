<?php

namespace App\Form\Category;

use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'Nom',
            ])
            ->add('company', TextType::class, [
                'required' => true,
                'label' => 'Nom',
            ])
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
