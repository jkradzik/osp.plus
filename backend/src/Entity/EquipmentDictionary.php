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
use App\Enum\EquipmentCategory;
use App\Repository\EquipmentDictionaryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EquipmentDictionaryRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(security: "is_granted('ROLE_ADMIN')"),
        new Patch(security: "is_granted('ROLE_ADMIN')"),
        new Delete(security: "is_granted('ROLE_ADMIN')"),
    ],
    order: ['name' => 'ASC'],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'name' => 'partial',
    'category' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['name', 'category'])]
class EquipmentDictionary
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public private(set) ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $name = null;

    #[ORM\Column(length: 20, enumType: EquipmentCategory::class)]
    #[Assert\NotBlank]
    public EquipmentCategory $category = EquipmentCategory::Other;

    #[ORM\Column]
    public bool $hasSizes = false;

    /** @var Collection<int, PersonalEquipment> */
    #[ORM\OneToMany(targetEntity: PersonalEquipment::class, mappedBy: 'type')]
    public private(set) Collection $equipment;

    public function __construct()
    {
        $this->equipment = new ArrayCollection();
    }
}
