<?php

namespace App\Security\Voter;

use App\Entity\Invoice;
use App\Service\CompanySession;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class InvoiceVoter extends Voter
{
    public const EDIT = 'edit';
    public const DELETE = 'delete';

    private CompanySession $companySession;

    public function __construct(CompanySession $companySession)
    {
        $this->companySession = $companySession;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE])
            && $subject instanceof \App\Entity\Invoice;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        return match ($attribute) {
            self::EDIT, self::DELETE => $this->canEdit($subject, $user),
            default => false,
        };
    }

    private function canEdit(Invoice $invoice, UserInterface $user): bool
    {
        $currentCompany = $this->companySession->getCurrentCompanyWithoutRedirect();
        if(!$currentCompany && !in_array('ROLE_ADMIN', $user->getRoles())) {
            return false;
        }

        return in_array('ROLE_ADMIN', $user->getRoles()) || $currentCompany !== null && $currentCompany === $invoice->getCompany() && $currentCompany->userInCompany($user);
    }
}
