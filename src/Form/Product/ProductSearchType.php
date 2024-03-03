<?php

namespace App\Form\Product;

use App\Entity\Category;
use App\Service\CompanySession;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductSearchType extends AbstractType
{
    private $companySession;

    public function __construct(CompanySession $companySession)
    {
        $this->companySession = $companySession;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $company = $this->companySession->getCurrentCompany();

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
                    'query_builder' => function (EntityRepository $er) use ($company) {
                        return $er->createQueryBuilder('c')
                            ->andWhere('c.company = :company')
                            ->setParameter('company', $company);
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
