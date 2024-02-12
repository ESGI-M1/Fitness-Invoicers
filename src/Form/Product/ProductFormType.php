<?php

namespace App\Form\Product;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
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
            ->add('slug', TextType::class, [
                'required' => true,
                'label' => 'Slug',
            ])
            ->add('ref', TextType::class, [
                'required' => true,
                'label' => 'Référence',
            ])
            ->add('price', TextType::class, [
                'required' => true,
                'label' => 'Prix',
            ])
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'label' => 'Photo de l\'entreprise',
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
                'csrf_protection' => false,
                'allow_extra_fields' => true,
                'attr' => [
                    'class' => 'add-form do-confirm',
                    'data-target' => '.modal-content',
                ],
                'update' => true,
            ]
        );
    }
}
