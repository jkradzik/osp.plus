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
use App\Enum\DecorationCategory;
use App\Repository\DecorationDictionaryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DecorationDictionaryRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(security: "is_granted('ROLE_ADMIN')"),
        new Patch(security: "is_granted('ROLE_ADMIN')"),
        new Delete(security: "is_granted('ROLE_ADMIN')"),
    ],
    order: ['sortOrder' => 'ASC', 'name' => 'ASC'],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'name' => 'partial',
    'category' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['sortOrder', 'name', 'category'])]
class DecorationDictionary
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public private(set) ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $name = null;

    #[ORM\Column(length: 20, enumType: DecorationCategory::class)]
    #[Assert\NotBlank]
    public DecorationCategory $category = DecorationCategory::Osp;

    #[ORM\Column(nullable: true)]
    public ?int $requiredYears = null;

    #[ORM\Column]
    public int $sortOrder = 0;

    /** @var Collection<int, Decoration> */
    #[ORM\OneToMany(targetEntity: Decoration::class, mappedBy: 'type')]
    public private(set) Collection $decorations;

    public function __construct()
    {
        $this->decorations = new ArrayCollection();
    }
}
