<?php

namespace App\Controller\User;

use App\Entity\CompanyMembership;
use App\Entity\User;
use App\Enum\CompanyMembershipStatusEnum;
use App\Form\User\UserFormType;
use App\Form\CompanyMembership\CompanyMembershipFormType;
use App\Form\User\UserSearchType;
use App\Service\CompanySession;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkNotification;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class CompanyMembershipController extends AbstractController
{
    #[Route('/companymembership', name: 'app_user_company_membership_index', methods: ['GET', 'POST'])]
    public function list(
        Request $request,
        CompanySession $companySession,
        EntityManagerInterface $entityManager,
        PaginatorInterface $paginator
    ): Response {
        $company = $companySession->getCurrentCompany();

        $form = $this->createForm(UserSearchType::class);

        $form->handleRequest($request);

        $users = $paginator->paginate(
            $entityManager->getRepository(CompanyMembership::class)
                ->getUsersMembershipsByFilters($company, $form->getData() ?? []),
            $request->query->getInt('page', 1),
            $request->query->getInt('items', 20)
        );

        return $this->render('companyMemberships/company_membership_index.html.twig', [
            'users' => $users,
            'form' => $form
        ]);
    }

    #[Route('companymembership/add', name: 'app_user_company_membership_add', methods: ['GET', 'POST'])]
    #[IsGranted('company_referent')]
    public function add(NotifierInterface $notifier, LoginLinkHandlerInterface $loginLinkHandler, UserPasswordHasherInterface $passwordHasher, Request $request, EntityManagerInterface $entityManager, CompanySession $companySession, MailerInterface $mailer): Response
    {
        $company = $companySession->getCurrentCompany();

        $user = new User();
        $form = $this->createForm(UserFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $email = $form->get('email')->getData();
            $userByMail = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            $companyMembership = new CompanyMembership();
            $companyMembership->setCompany($company);
            $companyMembership->setStatus(CompanyMembershipStatusEnum::ACCEPTED);

            if($userByMail){

                if($entityManager->getRepository(CompanyMembership::class)->getCompanyMembershipsByCompanyAndUser($company, $userByMail)){
                    $this->addFlash('error', 'Cet utilisateur est déjà membre de l\'entreprise');
                    return $this->redirectToRoute('app_user_company_membership_add');
                }
                $companyMembership->setRelatedUser($userByMail);

                $email = (new Email())
                    ->from('changeme@changeme.fr')
                    ->to($form->get('email')->getData())
                    ->subject('Invitation à rejoindre l\'entreprise : ' . $company->getName())
                    ->text('Bonjour, vous avez été invité à rejoindre l\'entreprise : ' . $company->getName())
                    ->html('<p>Bonjour, vous avez été invité à rejoindre l\'entreprise : ' . $company->getName() . '</p>');

                $mailer->send($email);

                $this->addFlash('success', 'Utilisateur ajouté avec succès!');

            }
            else{
                $companyMembership->setRelatedUser($user);
                $randomPassword = bin2hex(random_bytes(12));
                $user->setPassword($passwordHasher->hashPassword($user, $randomPassword));
                $user->setIsVerified(true);

                $entityManager->persist($user);

                $loginLinkDetails = $loginLinkHandler->createLoginLink($user);

                $notification = new LoginLinkNotification($loginLinkDetails, "Invitation à rejoindre l'entreprise : " . $company->getName());
                $recipient = new Recipient($user->getEmail());
                $notifier->send($notification, $recipient);

                $this->addFlash('success', 'Utilisateur ajouté avec succès! Un email a été envoyé à l\'utilisateur pour qu\'il puisse se connecter');

            }

            $entityManager->persist($companyMembership);
            $entityManager->flush();



            return $this->redirectToRoute('app_user_company_membership_index');
        }

        return $this->render('action.html.twig', [
            'action' => 'Ajouter un utilisateur',
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('companymembership/edit/{id}', name: 'app_user_company_membership_edit', methods: ['GET', 'POST'])]
    #[IsGranted('company_referent')]
    public function edit(Request $request, CompanyMembership $companyMembership, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CompanyMembershipFormType::class, $companyMembership);
        $user = $companyMembership->getRelatedUser();

        $form->get('firstName')->setData($user->getFirstName());
        $form->get('lastName')->setData($user->getLastName());
        $form->get('email')->setData($user->getEmail());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $companyMembership->getRelatedUser();
            $user->setFirstName($form->get('lastName'))
                ->setLastname($form->get('firstName'))
                ->setEmail($form->get('email'));

            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Company membership updated successfully!');
            return $this->redirectToRoute('app_user_company_membership_index');
        }

        return $this->render('action.html.twig', [
            'action' => 'Edit company membership',
            'form' => $form->createView(),
        ]);

    }

    #[Route('companymembership/delete/{id}/{token}', name: 'app_user_company_membership_delete', methods: ['GET'])]
    public function delete(CompanyMembership $companyMembership, EntityManagerInterface $entityManager, string $token): Response
    {

        if ($this->isCsrfTokenValid('delete-user-company-membership' . $companyMembership->getId(), $token)) {

            $user = $companyMembership->getRelatedUser();

            if($companyMembership->getCompany()->getReferent() === $user){
                $this->addFlash('error', 'Vous ne pouvez pas quitter l\'entreprise car vous êtes le référent de l\'entreprise');
                return $this->redirectToRoute('app_user_company_membership_index');
            }

            $user->removeCompanyMembership($companyMembership);
            $this->addFlash('success', 'Vous avez quitté l\'entreprise avec succès!');
            $entityManager->flush();

        }

        return $this->redirectToRoute('app_user_profile');
    }

}
