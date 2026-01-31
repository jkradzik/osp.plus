<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Enum\FinancialType;
use App\Repository\FinancialCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FinancialCategoryRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(security: "is_granted('ROLE_ADMIN')"),
        new Patch(security: "is_granted('ROLE_ADMIN')"),
        new Delete(security: "is_granted('ROLE_ADMIN')"),
    ],
    order: ['type' => 'ASC', 'sortOrder' => 'ASC', 'name' => 'ASC'],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'name' => 'partial',
    'type' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['sortOrder', 'name', 'type'])]
class FinancialCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public private(set) ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $name = null;

    #[ORM\Column(length: 20, enumType: FinancialType::class)]
    #[Assert\NotBlank]
    public FinancialType $type = FinancialType::Expense;

    #[ORM\Column]
    public int $sortOrder = 0;

    /** @var Collection<int, FinancialRecord> */
    #[ORM\OneToMany(targetEntity: FinancialRecord::class, mappedBy: 'category')]
    public private(set) Collection $records;

    public function __construct()
    {
        $this->records = new ArrayCollection();
    }
}
