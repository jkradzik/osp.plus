<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\FinancialRecordRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class FinancialSummaryController extends AbstractController
{
    public function __construct(
        private readonly FinancialRecordRepository $recordRepository,
    ) {}

    #[Route('/financial-summary', name: 'api_financial_summary', methods: ['GET'])]
    public function summary(Request $request): JsonResponse
    {
        // Check if user has access to view finances
        if (!$this->isGranted('ROLE_ADMIN')
            && !$this->isGranted('ROLE_PREZES')
            && !$this->isGranted('ROLE_SKARBNIK')
            && !$this->isGranted('ROLE_NACZELNIK')) {
            throw $this->createAccessDeniedException();
        }

        $year = $request->query->getInt('year') ?: null;
        $month = $request->query->getInt('month') ?: null;

        $summary = $this->recordRepository->getSummary($year, $month);

        return $this->json([
            'year' => $year,
            'month' => $month,
            ...$summary,
        ]);
    }
}
