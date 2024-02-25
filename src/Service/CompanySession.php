<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\Company;
use Doctrine\ORM\EntityManagerInterface;


class CompanySession
{
    public function __construct(
        private RequestStack $requestStack,
        private RouterInterface $router,
        private EntityManagerInterface $entityManager,
        private FormFactoryInterface $formFactory,
        private Security $security
    ) {

    }

    public function getCurrentCompany(): Company
    {
        $user = $this->security->getUser();

        $companyMembershipAccepted = $user->getCompanyMembershipAccepted();
        if (count($companyMembershipAccepted) == 1) {
            return $companyMembershipAccepted[0]->getCompany();
        }

        $session = $this->requestStack->getSession();
        $companyId = $session->get('current_company');

        if (!$companyId && !$this->security->isGranted('ROLE_SUPER_ADMIN')) {
            $response = new RedirectResponse($this->router->generate('app_user_company_set'), Response::HTTP_SEE_OTHER);
            $response->send();
        }

        $company = $this->entityManager->getRepository(Company::class)->find($companyId);

        if (!$company) {
            $response = new RedirectResponse($this->router->generate('app_user_company_set'), Response::HTTP_SEE_OTHER);
            $response->send();
        }

        return $company;
    }

    public function getCurrentCompanyWithoutRedirect(): Company | Null
    {
        $user = $this->security->getUser();

        $companyMembershipAccepted = $user->getCompanyMembershipAccepted();
        if (count($companyMembershipAccepted) == 1) {
            return $companyMembershipAccepted[0]->getCompany();
        }

        $session = $this->requestStack->getSession();
        $companyId = $session->get('current_company');

        if (!$companyId) {
            return null;
        }

        $company = $this->entityManager->getRepository(Company::class)->find($companyId);

        if (!$company) {
            return null;
        }

        return $company;
    }

    public function setCurrentCompany(Company $company): void
    {
        $session = $this->requestStack->getSession();
        $session->set('current_company', $company->getId());
    }

    public function getCurrentCompanyTwig(): Company | Null
    {

        $user = $this->security->getUser();

        $companyMembershipAccepted = $user->getCompanyMembershipAccepted();
        if (count($companyMembershipAccepted) == 1) {
            return $companyMembershipAccepted[0]->getCompany();
        }

        $session = $this->requestStack->getSession();
        $companyId = $session->get('current_company');

        if (!$companyId) {
            return null;
        }

        $company = $this->entityManager->getRepository(Company::class)->find($companyId);

        if (!$company) {
            return null;
        }

        return $company;
    }

}
