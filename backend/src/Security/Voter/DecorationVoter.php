<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Decoration;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Decoration>
 */
class DecorationVoter extends Voter
{
    public const VIEW = 'DECORATION_VIEW';
    public const EDIT = 'DECORATION_EDIT';
    public const DELETE = 'DECORATION_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof Decoration;
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
            self::DELETE => false, // Only admin can delete
            default => false,
        };
    }

    private function canView(User $user): bool
    {
        // All authenticated users can view decorations
        return true;
    }

    private function canEdit(User $user): bool
    {
        // Prezes can edit decorations
        return in_array('ROLE_PREZES', $user->roles);
    }
}
