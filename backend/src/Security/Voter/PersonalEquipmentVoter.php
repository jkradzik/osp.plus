<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\PersonalEquipment;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, PersonalEquipment>
 */
class PersonalEquipmentVoter extends Voter
{
    public const VIEW = 'EQUIPMENT_VIEW';
    public const EDIT = 'EQUIPMENT_EDIT';
    public const DELETE = 'EQUIPMENT_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && $subject instanceof PersonalEquipment;
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
        // All authenticated users can view equipment
        return true;
    }

    private function canEdit(User $user): bool
    {
        // Naczelnik can edit equipment
        return in_array('ROLE_NACZELNIK', $user->roles);
    }

    private function canDelete(User $user): bool
    {
        // Naczelnik can delete equipment
        return in_array('ROLE_NACZELNIK', $user->roles);
    }
}
