<?php

namespace App\Controller;

use App\Form\User\CompanyFormType;
use App\Service\CompanySession;
use App\Security\RightSociete;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class MainController extends AbstractController
{
    protected $companySession;
    protected $rights;

    public function __construct(CompanySession $companySession, RightSociete $rights)
    {
        $this->companySession = $companySession;
        $this->rights = $rights;
    }

    protected function societe()
    {
        $company = $this->companySession->getCurrentCompany();
        $right = $this->rights->hasRightOnSociete();

        if ($company instanceof RedirectResponse) {
            return false;
        }
        if (!$right) {
            throw $this->createNotFoundException('La page que vous cherchez est introuvable.');
        }

        return true;
    }
}
