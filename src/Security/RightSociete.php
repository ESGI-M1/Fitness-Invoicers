<?php

namespace App\Security;

use App\Entity\Company;
use App\Service\CompanySession;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class RightSociete
{
    private $companySession;
    private Security $security;

    public function __construct(CompanySession $companySession, Security $security)
    {
        $this->companySession = $companySession;
        $this->security = $security;
    }

    public function hasRightOnCompany()
    {
        $user = $this->security->getUser();
        if (($this->companySession->getCurrentCompany() instanceof Company &&
            $this->companySession->getCurrentCompany()->getReferent() === $user) ||
            $user->getRoles() === 'ROLE_ADMIN') {
            return true;
        }
        return false;
    }
}
