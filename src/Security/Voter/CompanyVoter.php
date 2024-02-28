<?php

namespace App\Security\Voter;

use App\Entity\Company;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class CompanyVoter extends Voter
{

    public const REFERENT = 'referent';

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::REFERENT === $attribute && $subject instanceof Company;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        return match ($attribute) {
            self::REFERENT => $this->isReferent($subject, $user),
            default => false,
        };
    }

    private function isReferent(Company $company, UserInterface $user): bool
    {
        return $company->getReferent() === $user;
    }

}
