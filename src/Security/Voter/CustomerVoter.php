<?php

namespace App\Security\Voter;

use App\Entity\Customer;
use App\Service\CompanySession;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\SecurityBundle\Security;

class CustomerVoter extends Voter
{
    public const SEE = 'see';
    public const ADD = 'add';
    public const EDIT = 'edit';
    public const DELETE = 'delete';

    private CompanySession $companySession;

    private Security $security;

    public function __construct(CompanySession $companySession, Security $security)
    {
        $this->companySession = $companySession;
        $this->security = $security;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {

        return in_array($attribute, [self::SEE, self::ADD, self::EDIT, self::DELETE])
            && $subject instanceof \App\Entity\Customer;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        return match ($attribute) {
            self::SEE, self::ADD => $this->canAddOrSee($subject, $user),
            self::EDIT => $this->canEdit($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            default => false,
        };
    }

    private function canAddOrSee(Customer $customer, UserInterface $user): bool
    {
        $currentCompany = $this->companySession->getCurrentCompanyWithoutRedirect();

        if($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if(!$currentCompany || $currentCompany !== $customer->getCompany() || !$currentCompany->userInCompany($user)) {
            return false;
        }

        return true;
    }

    private function canEdit(Customer $customer, UserInterface $user): bool
    {
        $currentCompany = $this->companySession->getCurrentCompanyWithoutRedirect();

        if($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if(!$currentCompany || $currentCompany !== $customer->getCompany() || !$currentCompany->userInCompany($user)) {
            return false;
        }

        return true;
    }

    private function canDelete(Customer $customer, UserInterface $user): bool
    {
        $currentCompany = $this->companySession->getCurrentCompanyWithoutRedirect();

        if($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if(!$currentCompany || $currentCompany !== $customer->getCompany() || !$currentCompany->userInCompany($user)) {
            return false;
        }

        return true;
    }

}
