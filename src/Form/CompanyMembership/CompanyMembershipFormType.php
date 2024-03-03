<?php

namespace App\Form\CompanyMembership;

use App\Entity\CompanyMembership;
use App\Enum\CompanyMembershipStatusEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CompanyMembershipFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lastName', TextType::class, [
                'required' => true,
                'label' => 'Nom',
                'mapped' => false
            ])
            ->add('firstName', TextType::class, [
                'required' => true,
                'label' => 'Prénom',
                'mapped' => false
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'Email',
                'mapped' => false
            ])
            ->add('status', EnumType::class, [
                'class' => CompanyMembershipStatusEnum::class,
                'label' => 'Status',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CompanyMembership::class,
        ]);
    }
}