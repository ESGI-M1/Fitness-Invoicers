<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use App\Entity\Company;

class CompanySession
{
    private RequestStack $requestStack;
    private RouterInterface $router;

    public function __construct(
        RequestStack $requestStack,
        RouterInterface $router
    ) {
        $this->requestStack = $requestStack;
        $this->router = $router;
    }

    public function setCurrentCompany(Company $company): void
    {
        $session = $this->requestStack->getSession();
        $session->set('current_company', $company);
    }

    public function getCurrentCompany(): Company | RedirectResponse
    {
        $session = $this->requestStack->getSession();
        $company = $session->get('current_company');

        if (!$company) {
            $url = $this->router->generate('app_user_company_set');
            return new RedirectResponse($url);
        }

        return $company;
    }

    public function isCurrentCompanySet(): bool
    {
        $session = $this->requestStack->getSession();
        return $session->has('current_company');
    }
}
