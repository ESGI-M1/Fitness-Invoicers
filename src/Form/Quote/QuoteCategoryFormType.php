<?php

namespace App\Form\Quote;

use App\Entity\Category;
use App\Service\CompanySession;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class QuoteCategoryFormType extends AbstractType
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
            ->add('categories', EntityType::class,
                [
                    'class' => Category::class,
                    'query_builder' => static function (EntityRepository $er) use ($company) {
                        return $er->createQueryBuilder('c')
                            ->andWhere('c.company IN (:company)')
                            ->setParameter('company', $company)
                            ->orderBy('c.name', 'DESC');
                    },
                    'placeholder' => '-------',
                    'choice_label' => 'name',
                    'label' => 'Categories',
                    'required' => false,
                    'multiple' => true,
                ])
        ;
    }

}
