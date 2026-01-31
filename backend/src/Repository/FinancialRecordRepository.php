<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\FinancialRecord;
use App\Enum\FinancialType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FinancialRecord>
 */
class FinancialRecordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FinancialRecord::class);
    }

    public function getSummary(?int $year = null, ?int $month = null): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT fr.type, fc.name as category_name, SUM(fr.amount) as total
            FROM financial_record fr
            JOIN financial_category fc ON fr.category_id = fc.id
            WHERE 1=1
        ';

        $params = [];

        if ($year !== null) {
            $sql .= ' AND EXTRACT(YEAR FROM fr.recorded_at) = :year';
            $params['year'] = $year;
        }

        if ($month !== null) {
            $sql .= ' AND EXTRACT(MONTH FROM fr.recorded_at) = :month';
            $params['month'] = $month;
        }

        $sql .= ' GROUP BY fr.type, fc.name';

        $results = $conn->executeQuery($sql, $params)->fetchAllAssociative();

        $summary = [
            'totalIncome' => '0.00',
            'totalExpense' => '0.00',
            'balance' => '0.00',
            'byCategory' => [],
        ];

        foreach ($results as $row) {
            $summary['byCategory'][] = [
                'type' => $row['type'],
                'category' => $row['category_name'],
                'total' => $row['total'],
            ];

            if ($row['type'] === 'income') {
                $summary['totalIncome'] = bcadd($summary['totalIncome'], $row['total'], 2);
            } else {
                $summary['totalExpense'] = bcadd($summary['totalExpense'], $row['total'], 2);
            }
        }

        $summary['balance'] = bcsub($summary['totalIncome'], $summary['totalExpense'], 2);

        return $summary;
    }
}
