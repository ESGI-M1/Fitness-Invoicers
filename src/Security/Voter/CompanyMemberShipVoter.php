<?php

namespace App\Security\Voter;

use App\Entity\Company;
use App\Entity\CompanyMembership;
use App\Service\CompanySession;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class CompanyMemberShipVoter extends Voter
{

    public const MEMBERSHIP = 'membership';
    public const REFERENT = 'referent';

    private CompanySession $companySession;

    public function __construct(CompanySession $companySession, Security $security)
    {
        $this->companySession = $companySession;
        $this->security = $security;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {

        if($attribute === 'membership' && $subject === null) {
            return true;
        }

        return self::REFERENT === $attribute && $subject instanceof CompanyMembership;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        return match ($attribute) {
            self::MEMBERSHIP => $this->canAccess($user),
            self::REFERENT => $this->isReferent($subject, $user),
            default => false,
        };
    }

    private function isReferent(CompanyMembership $companyMembership, UserInterface $user): bool
    {
        return $companyMembership->getCompany()->getReferent() === $user;
    }

    private function canAccess(UserInterface $user): bool
    {
        $currentCompany = $this->companySession->getCurrentCompanyWithoutRedirect();
        if($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return $currentCompany->userAcceptedInCompany($user);
    }
}
