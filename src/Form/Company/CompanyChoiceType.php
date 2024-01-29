<?php

namespace App\Form\Company;

use App\Entity\Company;
use App\Entity\CompanyMembership;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompanyChoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('company', ChoiceType::class, [
                'label' => 'Entreprise',
                'choices' => $options['companies'],
                'choice_label' => function (?CompanyMembership $companyMembership) {
                    return $companyMembership->getCompany()->getName();
                },
                'choice_value' => function (?CompanyMembership $companyMembership) {
                    return $companyMembership ? $companyMembership->getCompany()->getId() : '';
                },
                'placeholder' => 'Choisissez une entreprise',
                'required' => true,
                'mapped' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Valider',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'companies' => [],
        ]);
    }
}
