<?php

namespace App\Form\Company;

use App\Entity\Company;
use App\Entity\CompanyMembership;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class CompanyChoiceType extends AbstractType
{
    public function __construct(
        EntityManagerInterface $em,
    ) {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('company', EntityType::class, [
                'class' => CompanyMembership::class,
                'choices' => $options['companies'],
                'choice_label' => static function (CompanyMembership $com) {
                    return $com->getCompany()->getName();
                },
                'label' => 'Société',
                'placeholder' => 'Choisir une société',
                'required' => true,
                'multiple' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'companies' => [],
            ]
        );
    }
}
