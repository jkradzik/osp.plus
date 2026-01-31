<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\NumericFilter;
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
use App\Repository\FinancialRecordRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FinancialRecordRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Patch(),
        new Delete(security: "is_granted('ROLE_ADMIN')"),
    ],
    order: ['recordedAt' => 'DESC', 'createdAt' => 'DESC'],
    paginationItemsPerPage: 20,
)]
#[ApiFilter(SearchFilter::class, properties: [
    'type' => 'exact',
    'category' => 'exact',
    'description' => 'partial',
    'documentNumber' => 'partial',
])]
#[ApiFilter(DateFilter::class, properties: ['recordedAt'])]
#[ApiFilter(NumericFilter::class, properties: ['amount'])]
#[ApiFilter(OrderFilter::class, properties: ['recordedAt', 'amount', 'createdAt'])]
class FinancialRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public private(set) ?int $id = null;

    #[ORM\Column(length: 20, enumType: FinancialType::class)]
    #[Assert\NotBlank]
    public FinancialType $type = FinancialType::Expense;

    #[ORM\ManyToOne(targetEntity: FinancialCategory::class, inversedBy: 'records')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    public ?FinancialCategory $category = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank]
    #[Assert\Positive]
    public ?string $amount = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    public ?string $description = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    public ?string $documentNumber = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank]
    public ?\DateTimeInterface $recordedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    public ?User $createdBy = null;

    #[ORM\Column]
    public private(set) \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }
}
