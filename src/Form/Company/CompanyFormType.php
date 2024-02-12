<?php

namespace App\Form\Company;

use App\Entity\Company;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;


class CompanyFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'Nom',
            ])
            ->add('siret', TextType::class, [
                'required' => true,
                'label' => 'Siret'
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
                'data_class' => Company::class,
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
