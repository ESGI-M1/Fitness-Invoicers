<?php

namespace App\Security\Voter;

use App\Entity\Quote;
use App\Enum\QuoteStatusEnum;
use App\Service\CompanySession;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\SecurityBundle\Security;

class QuoteVoter extends Voter
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
        if($attribute === self::ADD && $subject === null) {
            return true;
        }

        return in_array($attribute, [self::SEE, self::EDIT, self::DELETE])
            && $subject instanceof \App\Entity\Quote;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        return match ($attribute) {
            self::SEE => $this->canAddOrSee($subject, $user),
            self::ADD => $this->canAddOrSee($subject, $user),
            self::EDIT => $this->canEdit($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            default => false,
        };
    }

    private function canAddOrSee(Quote $quote, UserInterface $user): bool
    {
        $currentCompany = $this->companySession->getCurrentCompanyWithoutRedirect();

        if($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return in_array('ROLE_ADMIN', $user->getRoles()) || $currentCompany !== null && $currentCompany === $quote->getCompany() && $currentCompany->userInCompany($user);
    }

    private function canEdit(Quote $quote, UserInterface $user): bool
    {
        $currentCompany = $this->companySession->getCurrentCompanyWithoutRedirect();

        if($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return in_array('ROLE_ADMIN', $user->getRoles()) || $currentCompany !== null && $currentCompany === $quote->getCompany() && $currentCompany->userInCompany($user) && $quote->getStatus() === QuoteStatusEnum::DRAFT;
    }

    private function canDelete(Quote $quote, UserInterface $user): bool
    {
        $currentCompany = $this->companySession->getCurrentCompanyWithoutRedirect();

        if($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return in_array('ROLE_ADMIN', $user->getRoles()) || $currentCompany !== null && $currentCompany === $quote->getCompany() && $currentCompany->userInCompany($user) && $quote->getStatus() === QuoteStatusEnum::DRAFT;
    }

}
