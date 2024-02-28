<?php

namespace App\Form\Quote;

use App\Entity\Quote;
use App\Service\CompanySession;
use App\Entity\Customer;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuoteCustomerFormType extends AbstractType
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
            ->add('customer', EntityType::class,
                [
                    'class' => Customer::class,
                    'query_builder' => static function (EntityRepository $er) use ($company) {
                        return $er->createQueryBuilder('c')
                            ->andWhere('c.company IN (:company)')
                            ->setParameter('company', $company);
                    },
                    'placeholder' => '-------',
                    'choice_label' => 'fullName',
                    'label' => 'Client',
                    'required' => false,
                    'attr' => [
                        'class' => 'select2'
                    ]
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Quote::class,
        ]);
    }
}
