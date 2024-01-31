<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use App\Entity\Company;
use Doctrine\ORM\EntityManagerInterface;


class CompanySession
{
    public function __construct(
        RequestStack $requestStack,
        RouterInterface $router,
        EntityManagerInterface $entityManager
    ) {
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->entityManager = $entityManager;
    }

    public function getCurrentCompany(): Company | RedirectResponse
    {
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

}
