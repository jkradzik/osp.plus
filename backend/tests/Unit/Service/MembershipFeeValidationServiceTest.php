<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Member;
use App\Entity\MembershipFee;
use App\Enum\FeeStatus;
use App\Enum\MembershipStatus;
use App\Repository\MembershipFeeRepository;
use App\Service\MembershipFeeValidationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class MembershipFeeValidationServiceTest extends TestCase
{
    private MembershipFeeRepository&MockObject $feeRepository;
    private EntityManagerInterface&MockObject $entityManager;
    private MembershipFeeValidationService $service;

    protected function setUp(): void
    {
        $this->feeRepository = $this->createMock(MembershipFeeRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->service = new MembershipFeeValidationService(
            $this->feeRepository,
            $this->entityManager,
        );
    }

    #[Test]
    public function isOverdue_unpaidBeforeDeadline_returnsFalse(): void
    {
        $fee = $this->createFee(2024, FeeStatus::Unpaid);
        $referenceDate = new \DateTimeImmutable('2024-03-15');

        $result = $this->service->isOverdue($fee, $referenceDate);

        self::assertFalse($result);
    }

    #[Test]
    public function isOverdue_unpaidAfterDeadline_returnsTrue(): void
    {
        $fee = $this->createFee(2024, FeeStatus::Unpaid);
        $referenceDate = new \DateTimeImmutable('2024-04-01');

        $result = $this->service->isOverdue($fee, $referenceDate);

        self::assertTrue($result);
    }

    #[Test]
    public function isOverdue_unpaidOnDeadlineDay_returnsFalse(): void
    {
        $fee = $this->createFee(2024, FeeStatus::Unpaid);
        $referenceDate = new \DateTimeImmutable('2024-03-31 12:00:00');

        $result = $this->service->isOverdue($fee, $referenceDate);

        self::assertFalse($result);
    }

    #[Test]
    public function isOverdue_unpaidOneDayAfterDeadline_returnsTrue(): void
    {
        $fee = $this->createFee(2024, FeeStatus::Unpaid);
        $referenceDate = new \DateTimeImmutable('2024-04-01 00:00:01');

        $result = $this->service->isOverdue($fee, $referenceDate);

        self::assertTrue($result);
    }

    #[Test]
    public function isOverdue_paidFee_returnsFalse(): void
    {
        $fee = $this->createFee(2024, FeeStatus::Paid);
        $referenceDate = new \DateTimeImmutable('2024-04-15');

        $result = $this->service->isOverdue($fee, $referenceDate);

        self::assertFalse($result);
    }

    #[Test]
    public function isOverdue_alreadyOverdueFee_returnsFalse(): void
    {
        $fee = $this->createFee(2024, FeeStatus::Overdue);
        $referenceDate = new \DateTimeImmutable('2024-04-15');

        $result = $this->service->isOverdue($fee, $referenceDate);

        self::assertFalse($result);
    }

    #[Test]
    public function isOverdue_exemptFee_returnsFalse(): void
    {
        $fee = $this->createFee(2024, FeeStatus::Exempt);
        $referenceDate = new \DateTimeImmutable('2024-04-15');

        $result = $this->service->isOverdue($fee, $referenceDate);

        self::assertFalse($result);
    }

    #[Test]
    public function isOverdue_notApplicableFee_returnsFalse(): void
    {
        $fee = $this->createFee(2024, FeeStatus::NotApplicable);
        $referenceDate = new \DateTimeImmutable('2024-04-15');

        $result = $this->service->isOverdue($fee, $referenceDate);

        self::assertFalse($result);
    }

    #[Test]
    public function markAsOverdueIfApplicable_qualifyingFee_marksAndReturnsTrue(): void
    {
        $fee = $this->createFee(2020, FeeStatus::Unpaid);

        $result = $this->service->markAsOverdueIfApplicable($fee);

        self::assertTrue($result);
        self::assertSame(FeeStatus::Overdue, $fee->status);
    }

    #[Test]
    public function markAsOverdueIfApplicable_paidFee_returnsFalseAndDoesNotChange(): void
    {
        $fee = $this->createFee(2020, FeeStatus::Paid);

        $result = $this->service->markAsOverdueIfApplicable($fee);

        self::assertFalse($result);
        self::assertSame(FeeStatus::Paid, $fee->status);
    }

    #[Test]
    public function markAsOverdueIfApplicable_exemptFee_returnsFalseAndDoesNotChange(): void
    {
        $fee = $this->createFee(2020, FeeStatus::Exempt);

        $result = $this->service->markAsOverdueIfApplicable($fee);

        self::assertFalse($result);
        self::assertSame(FeeStatus::Exempt, $fee->status);
    }

    #[Test]
    public function validateAndMarkAllOverdue_mixedFees_marksOnlyQualifying(): void
    {
        $unpaidOld = $this->createFee(2020, FeeStatus::Unpaid);
        $unpaidRecent = $this->createFee(2030, FeeStatus::Unpaid);
        $exempt = $this->createFee(2020, FeeStatus::Exempt);

        $this->feeRepository
            ->expects(self::once())
            ->method('findUnpaidFees')
            ->willReturn([$unpaidOld, $unpaidRecent, $exempt]);

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $result = $this->service->validateAndMarkAllOverdue();

        self::assertSame(1, $result);
        self::assertSame(FeeStatus::Overdue, $unpaidOld->status);
        self::assertSame(FeeStatus::Unpaid, $unpaidRecent->status);
        self::assertSame(FeeStatus::Exempt, $exempt->status);
    }

    #[Test]
    public function validateAndMarkAllOverdue_noQualifyingFees_returnsZeroAndDoesNotFlush(): void
    {
        $this->feeRepository
            ->expects(self::once())
            ->method('findUnpaidFees')
            ->willReturn([]);

        $this->entityManager
            ->expects(self::never())
            ->method('flush');

        $result = $this->service->validateAndMarkAllOverdue();

        self::assertSame(0, $result);
    }

    #[Test]
    public function getOverdueFees_delegatesToRepository(): void
    {
        $overdueFees = [
            $this->createFee(2020, FeeStatus::Overdue),
            $this->createFee(2021, FeeStatus::Overdue),
        ];

        $this->feeRepository
            ->expects(self::once())
            ->method('findOverdueFees')
            ->willReturn($overdueFees);

        $result = $this->service->getOverdueFees();

        self::assertSame($overdueFees, $result);
    }

    private function createFee(int $year, FeeStatus $status): MembershipFee
    {
        $member = new Member();
        $member->firstName = 'Test';
        $member->lastName = 'User';
        $member->pesel = '12345678901';
        $member->birthDate = new \DateTime('1990-01-01');
        $member->joinDate = new \DateTime('2020-01-01');
        $member->membershipStatus = MembershipStatus::Active;

        $fee = new MembershipFee();
        $fee->member = $member;
        $fee->year = $year;
        $fee->amount = '50.00';
        $fee->status = $status;

        return $fee;
    }
}
