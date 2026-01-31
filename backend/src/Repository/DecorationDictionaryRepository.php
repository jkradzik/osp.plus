<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\DecorationDictionary;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DecorationDictionary>
 */
class DecorationDictionaryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DecorationDictionary::class);
    }
}
