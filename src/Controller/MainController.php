<?php

namespace App\Controller;

use App\Entity\Company;
use App\Form\User\CompanyFormType;
use App\Service\CompanySession;
use App\Security\RightSociete;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class MainController extends AbstractController
{
    protected $companySession;
    protected $rights;
    protected $requestStack;

    public function __construct(CompanySession $companySession, RightSociete $rights, RequestStack $requestStack)
    {
        $this->companySession = $companySession;
        $this->rights = $rights;
        $this->requestStack = $requestStack;
    }

    protected function company()
    {
        $company = $this->companySession->getCurrentCompany();
        return $company;
    }

    public function render(string $view, array $parameters = [], ?Response $response = null): Response
    {
        $currentRoute = $this->requestStack->getCurrentRequest()->attributes->get('_route');

        if ($currentRoute !== 'app_user_company_set' && !$this->company() instanceof Company) {
            return $this->company();
        }

        $parameters['right'] = $this->rights->hasRightOnCompany();
        return parent::render($view, $parameters, $response);
    }
}
