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

    public const MAIL = 'mail';
    public const CONVERT = 'convert';

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

        return in_array($attribute, [self::SEE, self::EDIT, self::MAIL, self::CONVERT, self::DELETE])
            && $subject instanceof \App\Entity\Quote;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        return match ($attribute) {
            self::SEE => $this->canSee($subject, $user),
            self::ADD => $this->canAdd($subject, $user),
            self::EDIT => $this->canEdit($subject, $user),
            self::MAIL => $this->canMail($subject, $user),
            self::CONVERT => $this->canConvert($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            default => false,
        };
    }

    private function canSee(Quote $quote, UserInterface $user): bool
    {
        $currentCompany = $this->companySession->getCurrentCompanyWithoutRedirect();

        if($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if(!$currentCompany || $currentCompany !== $quote->getCompany() || !$currentCompany->userInCompany($user)) {
            return false;
        }

        return true;
    }

    private function canAdd(?Quote $invoice, UserInterface $user): bool
    {
        $currentCompany = $this->companySession->getCurrentCompanyWithoutRedirect();
        if($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if(!$currentCompany || !$currentCompany->userInCompany($user)) {
            return false;
        }

        return true;
    }

    private function canEdit(Quote $quote, UserInterface $user): bool
    {
        $currentCompany = $this->companySession->getCurrentCompanyWithoutRedirect();

        if($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if(!$currentCompany || $currentCompany !== $quote->getCompany() || !$currentCompany->userInCompany($user)) {
            return false;
        }

        return $quote->getStatus() === QuoteStatusEnum::DRAFT;
    }

    public function canConvert(Quote $quote, UserInterface $user): bool
    {
        $currentCompany = $this->companySession->getCurrentCompanyWithoutRedirect();

        if($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if(!$currentCompany || $currentCompany !== $quote->getCompany() || !$currentCompany->userInCompany($user)) {
            return false;
        }
        
        return $quote->getStatus() === QuoteStatusEnum::ACCEPTED && $quote->isValid() && $quote->getInvoices()->isEmpty();
    }

    private function canMail(Quote $quote, UserInterface $user): bool
    {
        $currentCompany = $this->companySession->getCurrentCompanyWithoutRedirect();
        if($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if(!$currentCompany || $currentCompany !== $quote->getCompany() || !$currentCompany->userInCompany($user)) {
            return false;
        }

        return $quote->getStatus() === QuoteStatusEnum::PENDING || $quote->getStatus() === QuoteStatusEnum::ACCEPTED || $quote->getStatus() === QuoteStatusEnum::SENT;
    }

    private function canDelete(Quote $quote, UserInterface $user): bool
    {
        $currentCompany = $this->companySession->getCurrentCompanyWithoutRedirect();

        if($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if(!$currentCompany || $currentCompany !== $quote->getCompany() || !$currentCompany->userInCompany($user)) {
            return false;
        }

        return $quote->getStatus() === QuoteStatusEnum::DRAFT;
    }

}
