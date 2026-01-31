<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\PersonalEquipmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PersonalEquipmentRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_NACZELNIK')"),
        new Patch(security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_NACZELNIK')"),
        new Delete(security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_NACZELNIK')"),
    ],
    order: ['issuedAt' => 'DESC'],
    paginationItemsPerPage: 20,
)]
#[ApiFilter(SearchFilter::class, properties: [
    'member' => 'exact',
    'type' => 'exact',
    'size' => 'partial',
    'serialNumber' => 'partial',
])]
#[ApiFilter(DateFilter::class, properties: ['issuedAt'])]
#[ApiFilter(OrderFilter::class, properties: ['issuedAt', 'createdAt'])]
class PersonalEquipment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public private(set) ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Member::class, inversedBy: 'personalEquipment')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    public ?Member $member = null;

    #[ORM\ManyToOne(targetEntity: EquipmentDictionary::class, inversedBy: 'equipment')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    public ?EquipmentDictionary $type = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Length(max: 20)]
    public ?string $size = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    public ?string $serialNumber = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank]
    public ?\DateTimeInterface $issuedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public ?string $notes = null;

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
