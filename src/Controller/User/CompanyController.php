<?php

namespace App\Controller\User;

use App\Entity\Company;
use App\Entity\CompanyMembership;
use App\Enum\CompanyMembershipStatusEnum;
use App\Service\CompanySession;
use App\Form\Company\CompanyFormType;
use App\Form\Company\CompanyChoiceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Controller\MainController;


class CompanyController extends MainController
{
    #[Route('/company', name: 'app_user_company_index')]
    public function list(CompanySession $companySession, EntityManagerInterface $entityManager): Response
    {
        $company = $companySession->getCurrentCompany();

        return $this->render('companies/company_index.html.twig', [
            'company' => $company,
        ]);
    }

    #[Route('/company/show/{id}', name: 'app_user_company_show')]
    public function show(Company $company): Response
    {
        return $this->render('companies/company_show.html.twig', [
            'company' => $company,
        ]);
    }

    #[Route('company/add', name: 'app_user_company_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $company = new Company();
        $form = $this->createForm(CompanyFormType::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $this->getUser();
            $companyMembership = new CompanyMembership();
            $companyMembership->setRelatedUser($user);
            $companyMembership->setCompany($company);
            $companyMembership->setStatus(CompanyMembershipStatusEnum::ACCEPTED);
            $entityManager->persist($companyMembership);


            $company->setReferent($user);
            $company->addCompanyMembership($companyMembership);

            $entityManager->persist($company);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_company_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('action.html.twig', [
            'action' => 'Ajouter une entreprise',
            'company' => $company,
            'form' => $form,
        ]);
    }

    #[Route('company/edit/{id}', name: 'app_user_company_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Company $company, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CompanyFormType::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->flush();

            return $this->redirectToRoute('app_user_company_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('action.html.twig', [
            'action' => 'Modifier l\'entreprise n°' . $company->getId(),
            'company' => $company,
            'form' => $form,
        ]);
    }

    #[Route('company/delete/{id}/{token}', name: 'app_user_company_delete', methods: ['POST'])]
    public function delete(Request $request, Company $company, EntityManagerInterface $entityManager, string $token): Response
    {
        if ($this->isCsrfTokenValid('delete'.$company->getId(), $token)) {
            $entityManager->remove($company);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_company_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('company/set', name: 'app_user_company_set', methods: ['GET', 'POST'])]
    public function set(Request $request, EntityManagerInterface $entityManager, CompanySession $companySession): Response
    {
        $user = $this->getUser();
        $companies = $user->getCompanyMembershipAccepted();

        $form = $this->createForm(CompanyChoiceType::class, null, ['companies' => $companies]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $this->getUser();

            $companyMembershipForm = $form->get('company')->getData();
            if($companyMembershipForm->getRelatedUser() === $user && $companyMembershipForm->getStatus() === CompanyMembershipStatusEnum::ACCEPTED) {
                $companySession->setCurrentCompany($companyMembershipForm->getCompany());
            }

            return $this->redirectToRoute('app_index');
        }

        return $this->render('action.html.twig', [
            'action' => 'Choisir une entreprise',
            'form' => $form,
        ]);
    }
}
