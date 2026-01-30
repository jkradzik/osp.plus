<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\MembershipFee;
use App\Enum\FeeStatus;
use App\Repository\MembershipFeeRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class MembershipFeeValidationService
{
    private const DEADLINE_MONTH = 3;
    private const DEADLINE_DAY = 31;

    public function __construct(
        private MembershipFeeRepository $feeRepository,
        private EntityManagerInterface $entityManager,
    ) {}

    public function isOverdue(MembershipFee $fee, ?\DateTimeInterface $referenceDate = null): bool
    {
        if ($this->isExemptFromValidation($fee)) {
            return false;
        }

        if ($fee->status !== FeeStatus::Unpaid) {
            return false;
        }

        $referenceDate ??= new \DateTimeImmutable();
        $deadline = $this->getDeadlineForYear($fee->year);

        return $referenceDate > $deadline;
    }

    public function markAsOverdueIfApplicable(MembershipFee $fee): bool
    {
        if (!$this->isOverdue($fee)) {
            return false;
        }

        $fee->status = FeeStatus::Overdue;

        return true;
    }

    public function validateAndMarkAllOverdue(): int
    {
        $unpaidFees = $this->feeRepository->findUnpaidFees();
        $markedCount = 0;

        foreach ($unpaidFees as $fee) {
            if ($this->markAsOverdueIfApplicable($fee)) {
                $markedCount++;
            }
        }

        if ($markedCount > 0) {
            $this->entityManager->flush();
        }

        return $markedCount;
    }

    /**
     * @return MembershipFee[]
     */
    public function getOverdueFees(): array
    {
        return $this->feeRepository->findOverdueFees();
    }

    private function isExemptFromValidation(MembershipFee $fee): bool
    {
        return in_array($fee->status, [
            FeeStatus::Exempt,
            FeeStatus::NotApplicable,
        ], true);
    }

    private function getDeadlineForYear(?int $year): \DateTimeImmutable
    {
        $year ??= (int) date('Y');

        return new \DateTimeImmutable(sprintf(
            '%d-%02d-%02d 23:59:59',
            $year,
            self::DEADLINE_MONTH,
            self::DEADLINE_DAY
        ));
    }
}
