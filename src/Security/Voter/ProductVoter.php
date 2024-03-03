<?php

namespace App\Security\Voter;

use App\Entity\Product;
use App\Service\CompanySession;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\SecurityBundle\Security;

class ProductVoter extends Voter
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
            && $subject instanceof \App\Entity\Product;
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

    private function canAddOrSee(Product $product, UserInterface $user): bool
    {
        $currentCompany = $this->companySession->getCurrentCompanyWithoutRedirect();

        if($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if(!$currentCompany || $currentCompany !== $product->getCompany() || !$currentCompany->userInCompany($user)) {
            return false;
        }

        return true;
    }

    private function canEdit(Product $product, UserInterface $user): bool
    {
        $currentCompany = $this->companySession->getCurrentCompanyWithoutRedirect();

        if($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if(!$currentCompany || $currentCompany !== $product->getCompany() || !$currentCompany->userInCompany($user)) {
            return false;
        }

        return true;
    }

    private function canDelete(Product $product, UserInterface $user): bool
    {
        $currentCompany = $this->companySession->getCurrentCompanyWithoutRedirect();

        if($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if(!$currentCompany || $currentCompany !== $product->getCompany() || !$currentCompany->userInCompany($user)) {
            return false;
        }

        return true;
    }

}
