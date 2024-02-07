<?php

namespace App\Form\Company;

use App\Entity\Company;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompanyFormType extends AbstractType
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
                'label' => 'Nom'
            ])
            ->add('siret', TextType::class, [
                'required' => true,
                'label' => 'Siret'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Company::class,
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
