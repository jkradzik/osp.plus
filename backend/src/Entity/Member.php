<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Enum\MembershipStatus;
use App\Repository\MemberRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MemberRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['pesel'], message: 'Ten PESEL jest już zarejestrowany')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Patch(),
        new Delete(security: "is_granted('ROLE_ADMIN')"),
    ],
    order: ['lastName' => 'ASC', 'firstName' => 'ASC'],
)]
class Member
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public private(set) ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public ?string $firstName = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public ?string $lastName = null;

    public string $fullName {
        get => trim(($this->firstName ?? '') . ' ' . ($this->lastName ?? ''));
    }

    #[ORM\Column(length: 11, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(exactly: 11)]
    #[Assert\Regex(pattern: '/^\d{11}$/', message: 'PESEL musi składać się z 11 cyfr')]
    public ?string $pesel = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public ?string $address = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Length(max: 20)]
    public ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    public ?string $email = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank]
    public ?\DateTimeInterface $birthDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank]
    public ?\DateTimeInterface $joinDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    public ?\DateTimeInterface $deathDate = null;

    #[ORM\Column(length: 20, enumType: MembershipStatus::class)]
    #[Assert\NotBlank]
    public MembershipStatus $membershipStatus = MembershipStatus::Active;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    public ?string $boardPosition = null;

    #[ORM\Column]
    public private(set) \DateTimeImmutable $createdAt;

    /** @var Collection<int, MembershipFee> */
    #[ORM\OneToMany(targetEntity: MembershipFee::class, mappedBy: 'member', cascade: ['persist', 'remove'], orphanRemoval: true)]
    public private(set) Collection $membershipFees;

    public function __construct()
    {
        $this->membershipFees = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function addMembershipFee(MembershipFee $fee): void
    {
        if (!$this->membershipFees->contains($fee)) {
            $this->membershipFees->add($fee);
            $fee->member = $this;
        }
    }

    public function removeMembershipFee(MembershipFee $fee): void
    {
        if ($this->membershipFees->removeElement($fee)) {
            if ($fee->member === $this) {
                $fee->member = null;
            }
        }
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }
}
