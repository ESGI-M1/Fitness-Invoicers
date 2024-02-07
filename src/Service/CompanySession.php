<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
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
    ) { }

    public function getCurrentCompany(): Company | RedirectResponse
    {
        $user = $this->security->getUser();

        // To Do rajouter, company valide uniquement

        if (count($user->getCompanyMemberships()) == 1) {
            return $user->getCompanyMemberships()[0]->getCompany();
        }

        $session = $this->requestStack->getSession();
        $companyId = $session->get('current_company');

        if (!$companyId) {
            $url = $this->router->generate('app_user_company_set');
            return new RedirectResponse($url);
        }

        $company = $this->entityManager->getRepository(Company::class)->find($companyId);

        if (!$company) {
            $url = $this->router->generate('app_user_company_set');
            return new RedirectResponse($url);
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

        if(count($user->getCompanyMemberships()) == 1){
            return $user->getCompanyMemberships()[0]->getCompany();
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
