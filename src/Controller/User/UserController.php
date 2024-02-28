<?php

namespace App\Controller\User;

use App\Form\User\ProfileFormType;
use App\Form\ChangePasswordFormType;
use App\Form\CompanyMembership\CompanyMembershipFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class UserController extends AbstractController
{
    
    #[Route('/profile', name: 'app_user_profile', methods: ['GET', 'POST'])]
    public function profile(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();

        $profileForm = $this->createForm(ProfileFormType::class, $user);
        $passwordForm = $this->createForm(ChangePasswordFormType::class);
        //$companyForm = $this->createForm(CompanyFormType::class, $this->getUser());

        $companyMemberships = $user->getCompanyMemberships()->getValues();
        dump($companyMemberships);

        $profileForm->handleRequest($request);

        if ($profileForm->isSubmitted() && $profileForm->isValid()) {

            // add flash message
            $this->addFlash('success', 'Profile updated successfully!');

            $entityManager->flush();
            return $this->redirectToRoute('app_index_profile');
        }

        $passwordForm->handleRequest($request);

        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {

            $currentPassword = $passwordForm->get('currentPassword')->getData();
            $checkPass = $passwordHasher->isPasswordValid($user, $currentPassword);

            if(($checkPass) === false) {
                $this->addFlash('error', 'The current password is incorrect');
                return $this->redirectToRoute('app_user_profile');
            }

            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $passwordForm->get('plainPassword')->getData()
                )
            );
            $entityManager->flush();
            $this->addFlash('success', 'Password updated successfully!');
            return $this->redirectToRoute('app_user_profile');
        }

        return $this->render('dashboard/profile.html.twig', [
            'profileForm' => $profileForm->createView(),
            'passwordForm' => $passwordForm->createView(),
            'companyMemberships' => $companyMemberships
        ]);
    }

    #[Route('user/invitation', name: 'app_user_invitation', methods: ['GET', 'POST'])]
    public function invitation(Request $request, EntityManagerInterface $entityManager): Response
    {

        $this->addFlash('success', 'Veuillez renseignez votre mot de passe pour vous connecter plus tard');

        return $this->redirectToRoute('app_user_profile');
    }


}
