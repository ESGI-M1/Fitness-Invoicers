<?php

namespace App\Form\Product;

use App\Entity\Product;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ProductFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
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
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c');
                },
                'attr' => [
                    'class' => 'select2'
                ],
                'by_reference' => false,
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
                'attr' => [
                    'class' => 'add-form do-confirm',
                ],
                'update' => true,
            ]
        );
    }
}
