<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => [
                    'placeholder' => 'exemple@exemple.fr',
                    'autocomplete' => 'email'
                ],
                'constraints' =>[
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Votre email doit être de minimum {{ limit }} caractères',
                    ]),
                    new NotBlank([
                        'message' => 'Merci d\'entrer votre mail'
                    ])
                ]
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'placeholder' => 'Ex : Jean',
                    'autocomplete' => 'given-name'
                ],
                'constraints' =>[
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Votre prénom doit être de minimum {{ limit }} caractères',
                    ]),
                    new NotBlank([
                        'message' => 'Merci d\'entrer votre prénom'
                    ])
                ]
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Ex : Dupont',
                    'autocomplete' => 'family-name'
                ],
                'constraints' =>[
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Votre nom doit être de minimum {{ limit }} caractères',
                    ]),
                    new NotBlank([
                        'message' => 'Merci d\'entrer votre nom'
                    ])
                ]
            ])
            ->add('companyName',TextType::class,[
                'label' => 'Nom de la société',
                'attr' => [
                    'placeholder' => 'Ex : Fitness Invoicer',
                    'autocomplete' => 'organization'
                ],
                'mapped' => false,
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Le nom de la société doit être de minimum {{ limit }} caractères',
                    ]),
                    new NotBlank([
                        'message' => 'Merci d\'entrer le nom de la société'
                    ]),
                ],
            ])
            ->add('siret', TextType::class,[
                'label' => 'Numéro de SIRET',
                'attr' => [
                    'placeholder' => 'Ex : 12345678912345',
                    'autocomplete' => 'siret'
                ],
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci d\'entrer le numéro de SIRET'
                    ]),
                    new Regex([
                        'pattern' => '/^[0-9]{14}$/',
                        'message' => 'Le numéro de SIRET doit être composé de 14 chiffres'
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
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
