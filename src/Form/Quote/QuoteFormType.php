<?php

namespace App\Form\Quote;

use App\Entity\Deposit;
use App\Entity\Invoice;
use App\Entity\Item;
use App\Entity\Quote;
use App\Enum\QuoteStatusEnum;
use App\Service\CompanySession;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuoteFormType extends AbstractType
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
            ->add('discountAmount', NumberType::class, [
                'required' => false,
                'label' => 'Montant de la remise',
            ])
            ->add('items', EntityType::class,
                [
                    'class' => Item::class,
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->createQueryBuilder('i');
                    },
                    'placeholder' => '-------',
                    'choice_label' => 'product.name',
                    'label' => 'Item',
                    'required' => true,
                    'multiple' => true,
                    'attr' => [
                        'class' => 'select2'
                    ]
                ])
            ->add('deposits', EntityType::class,
                [
                    'class' => Deposit::class,
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->createQueryBuilder('d');
                    },
                    'placeholder' => '-------',
                    'choice_label' => 'price',
                    'label' => 'Rapports',
                    'required' => false,
                    'multiple' => true,
                    'attr' => [
                        'class' => 'select2'
                    ]
                ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Quote::class,
        ]);
    }
}
