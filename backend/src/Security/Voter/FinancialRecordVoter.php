<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\FinancialRecord;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, FinancialRecord>
 */
class FinancialRecordVoter extends Voter
{
    public const VIEW = 'FINANCIAL_VIEW';
    public const EDIT = 'FINANCIAL_EDIT';
    public const DELETE = 'FINANCIAL_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof FinancialRecord;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        // Admin can do everything
        if (in_array('ROLE_ADMIN', $user->roles)) {
            return true;
        }

        return match ($attribute) {
            self::VIEW => $this->canView($user),
            self::EDIT => $this->canEdit($user),
            self::DELETE => $this->canDelete($user),
            default => false,
        };
    }

    private function canView(User $user): bool
    {
        // Prezes, Skarbnik, Naczelnik can view finances
        // Regular users (Druh) cannot
        return in_array('ROLE_PREZES', $user->roles)
            || in_array('ROLE_SKARBNIK', $user->roles)
            || in_array('ROLE_NACZELNIK', $user->roles);
    }

    private function canEdit(User $user): bool
    {
        // Only Skarbnik can edit finances
        return in_array('ROLE_SKARBNIK', $user->roles);
    }

    private function canDelete(User $user): bool
    {
        // Only Skarbnik can delete finances
        return in_array('ROLE_SKARBNIK', $user->roles);
    }
}
