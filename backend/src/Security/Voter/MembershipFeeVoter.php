<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\MembershipFee;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, MembershipFee>
 */
class MembershipFeeVoter extends Voter
{
    public const VIEW = 'FEE_VIEW';
    public const EDIT = 'FEE_EDIT';
    public const DELETE = 'FEE_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof MembershipFee;
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
        // All authenticated users can view fees
        return true;
    }

    private function canEdit(User $user): bool
    {
        // Only Skarbnik can edit fees
        return in_array('ROLE_SKARBNIK', $user->roles);
    }

    private function canDelete(User $user): bool
    {
        // Only Skarbnik can delete fees
        return in_array('ROLE_SKARBNIK', $user->roles);
    }
}
