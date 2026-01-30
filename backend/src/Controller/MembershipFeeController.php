<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\MembershipFeeValidationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/membership-fees')]
final class MembershipFeeController extends AbstractController
{
    public function __construct(
        private readonly MembershipFeeValidationService $validationService,
    ) {}

    #[Route('/validate-overdue', name: 'api_membership_fees_validate_overdue', methods: ['POST'])]
    public function validateOverdue(): JsonResponse
    {
        $markedCount = $this->validationService->validateAndMarkAllOverdue();

        return $this->json([
            'success' => true,
            'marked_count' => $markedCount,
            'message' => $markedCount > 0
                ? "Oznaczono {$markedCount} składek jako zaległe."
                : 'Brak składek do oznaczenia jako zaległe.',
        ]);
    }

    #[Route('/overdue', name: 'api_membership_fees_overdue', methods: ['GET'])]
    public function getOverdue(): JsonResponse
    {
        $overdueFees = $this->validationService->getOverdueFees();

        $data = array_map(fn($fee) => [
            'id' => $fee->id,
            'member_id' => $fee->member?->id,
            'member_name' => $fee->member?->fullName,
            'year' => $fee->year,
            'amount' => $fee->amount,
            'status' => $fee->status->value,
        ], $overdueFees);

        return $this->json([
            'count' => count($data),
            'items' => $data,
        ]);
    }
}
