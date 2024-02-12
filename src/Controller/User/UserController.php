<?php

namespace App\Controller\User;

use App\Entity\CompanyMembership;
use App\Entity\User;
use App\Form\User\ProfileFormType;
use App\Form\User\UserFormType;
use App\Service\CompanySession;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class UserController extends AbstractController
{

    #[Route('/user', name: 'app_user_user_index', methods: ['GET', 'POST'])]
    public function user(Request $request, CompanySession $companySession, EntityManagerInterface $entityManager, PaginatorInterface $paginator): Response
    {
        $company = $companySession->getCurrentCompany();
        if($company instanceof RedirectResponse) {
            return $company;
        }

        //dd($entityManager->getRepository(CompanyMembership::class)->getCompanyMembershipsByCompany($company));

        $users = $paginator->paginate(
            $entityManager->getRepository(CompanyMembership::class)->getCompanyMembershipsByCompany($company),
            $request->query->getInt('page', 1),
            $request->query->getInt('items', 20)
        );

        $form = null;
        if($this->isGranted('add_user')) {
            $form = $this->createForm(UserFormType::class);
        }

        return $this->render('users/user_index.html.twig', [
            'users' => $users,
            'form' => $form
        ]);

    }

    #[Route('user/add', name: 'app_user_user_add', methods: ['GET', 'POST'])]
    #[IsGranted('company_referent')]
    public function add(Request $request, EntityManagerInterface $entityManager, CompanySession $companySession): Response
    {
        $company = $companySession->getCurrentCompany();
        if($company instanceof RedirectResponse) {
            return $company;
        }

        $user = new User();
        $form = $this->createForm(UserFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            dd($form->getData(),$company);

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_user_index');
        }

        return $this->render('action.html.twig', [
            'action' => 'Ajouter un utilisateur',
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/profile', name: 'app_user_profile', methods: ['GET', 'POST'])]
    public function profile(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        $profileForm = $this->createForm(ProfileFormType::class, $user);
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

        return $this->render('dashboard/profile.html.twig', [
            'profileForm' => $profileForm->createView(),
            'companyMemberships' => $companyMemberships
        ]);
    }

    #[Route('user/companymembership/delete/{id}/{token}', name: 'app_user_company_membership_delete', methods: ['GET'])]
    public function deleteCompanyMembership(CompanyMembership $companyMembership, EntityManagerInterface $entityManager, string $token): Response
    {

        if($this->isCsrfTokenValid('delete-user-company-membership'.$companyMembership->getId(), $token)) {

            $user = $this->getUser();
            $user->removeCompanyMembership($companyMembership);
            $entityManager->flush();
            $this->addFlash('success', 'Vous avez quitté l\'entreprise avec succès!');
        }

        return $this->redirectToRoute('app_user_profile');
    }

}
