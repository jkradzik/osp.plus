<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\EquipmentDictionary;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EquipmentDictionary>
 */
class EquipmentDictionaryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EquipmentDictionary::class);
    }
}
