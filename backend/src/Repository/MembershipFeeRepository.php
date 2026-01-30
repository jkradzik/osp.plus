<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MembershipFee;
use App\Enum\FeeStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MembershipFee>
 */
class MembershipFeeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MembershipFee::class);
    }

    /**
     * @return MembershipFee[]
     */
    public function findUnpaidFees(): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.status = :status')
            ->setParameter('status', FeeStatus::Unpaid)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return MembershipFee[]
     */
    public function findOverdueFees(): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.status = :status')
            ->setParameter('status', FeeStatus::Overdue)
            ->getQuery()
            ->getResult();
    }
}
