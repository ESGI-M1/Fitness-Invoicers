<?php

namespace App\Form\User;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use App\Enum\CivilityEnum;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Doctrine\ORM\EntityManagerInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;



class ProfileFormType extends AbstractType
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Security
     */
    private $security;

    /**
     * DocumentHeaderType constructor.
     * @param EntityManagerInterface $em
     * @param Security $security
     */
    public function __construct(
        EntityManagerInterface $em,
        Security               $security,
    ) {
        $this->em = $em;
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastName', TextType::class, [
                'required' => true,
                'label' => 'Nom',
            ])
            ->add('firstName', TextType::class, [
                'required' => true,
                'label' => 'Prénom',
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'Email',
            ])
            ->add('civility', ChoiceType::class, [
                'choices' => [
                    'Homme' => CivilityEnum::MALE,
                    'Femme' => CivilityEnum::FEMALE,
                    'Autre' => CivilityEnum::OTHER,
                ],
                'label' => 'Sexe'
            ])
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'label' => 'Photo de profil',
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
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
