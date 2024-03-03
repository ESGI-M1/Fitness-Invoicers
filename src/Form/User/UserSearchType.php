<?php

namespace App\Form\User;

use App\Entity\Company;
use App\Enum\CivilityEnum;
use App\Enum\CompanyMembershipStatusEnum;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserSearchType extends AbstractType
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $status = [];
        foreach (CompanyMembershipStatusEnum::cases() as $statut) {
            $status[$statut->name] = $statut;
        }

        $civilities = [];
        foreach (CivilityEnum::cases() as $civility) {
            $civilities[$civility->name] = $civility;
        }

        $request = $this->requestStack->getCurrentRequest();
        $routeName = $request->attributes->get('_route');

        $builder
            ->add('alias', TextType::class, [
                'label' => 'Nom',
                'required' => false
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'Email',
            ])
            ->add('civility', ChoiceType::class, [
                'choices' => $civilities,
                'placeholder' => 'Tous',
                'label' => 'Sexe',
                'attr' => [
                    'class' => 'select2'
                ]
            ]);

        if ($routeName === 'app_admin_user_index') {
            $builder
                ->add('company', EntityType::class,
                    [
                        'class' => Company::class,
                        'query_builder' => static function (EntityRepository $er) {
                            return $er->createQueryBuilder('c');
                        },
                        'placeholder' => 'Toutes',
                        'choice_label' => 'name',
                        'label' => 'Entreprise',
                        'attr' => [
                            'class' => 'select2'
                        ],
                        'required' => false
                    ]
                );
        } elseif ($routeName === 'app_user_company_membership_index') {
            $builder
                ->add('status', ChoiceType::class, [
                    'choices' => $status,
                    'label' => 'Statut',
                    'placeholder' => 'Tous',
                    'attr' => [
                        'class' => 'select2'
                    ],
                    'choice_label' => function ($statut) {
                        return $statut->name;
                    }
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
    }
}
