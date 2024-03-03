<?php

namespace App\Form\Product;

use App\Entity\Product;
use App\Entity\Category;
use App\Service\CompanySession;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ProductFormType extends AbstractType
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
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'Nom',
            ])
            ->add('reference', TextType::class, [
                'required' => true,
                'label' => 'Référence',
            ])
            ->add('price', TextType::class, [
                'required' => true,
                'label' => 'Prix',
            ])
            ->add('categories', EntityType::class, [
                'class' => Category::class,
                'query_builder' => function (EntityRepository $er) use ($company) {
                    return $er->createQueryBuilder('c')
                        ->andWhere('c.company = :company')
                        ->setParameter('company', $company);
                },
                'attr' => [
                    'class' => 'select2 w-full'
                ],
                'placeholder' => '-------',
                'choice_label' => 'name',
                'label' => 'Catégorie',
                'required' => false,
                'multiple' => true,
            ])
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'label' => 'Photo du produit',
                'download_uri' => false,
                'image_uri' => false,
                'allow_delete' => false,
                'delete_label' => 'Supprimer',
                'download_label' => 'Télécharger',
                'download_uri' => false,
                'asset_helper' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Product::class,
                'update' => true,
            ]
        );
    }
}
