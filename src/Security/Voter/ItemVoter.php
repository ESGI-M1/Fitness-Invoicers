<?php

namespace App\Security\Voter;

use App\Entity\Item;
use App\Enum\InvoiceStatusEnum;
use App\Enum\QuoteStatusEnum;
use App\Service\CompanySession;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ItemVoter extends Voter
{
    public const EDIT = 'edit';

    private CompanySession $companySession;

    private Security $security;

    public function __construct(CompanySession $companySession, Security $security)
    {
        $this->companySession = $companySession;
        $this->security = $security;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {

        return $attribute === self::EDIT && $subject instanceof \App\Entity\Item;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        return match ($attribute) {
            self::EDIT => $this->canEdit($subject, $user),
            default => false,
        };
    }

    private function canEdit(Item $item, UserInterface $user): bool
    {
        $currentCompany = $this->companySession->getCurrentCompanyWithoutRedirect();
        if($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        $invoice = $item->getInvoices();
        $quote = $item->getQuote();

        if(!$currentCompany || $invoice && $currentCompany !== $invoice->getCompany() || !$currentCompany->userInCompany($user)) {
            return false;
        }

        if($invoice && $invoice->getStatus() !== InvoiceStatusEnum::DRAFT && $quote && $quote->getStatus() !== QuoteStatusEnum::DRAFT) {
            return false;
        }

        return ($invoice && $invoice->getStatus() === InvoiceStatusEnum::DRAFT) || ($quote && $quote->getStatus() === QuoteStatusEnum::DRAFT);
    }

}
