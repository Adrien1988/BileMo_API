<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
#[ORM\Table(name: 'client')]
#[UniqueEntity(fields: ['name'], groups: ['client:write'])]
#[ApiResource(
    operations: [
        new Get(security: "is_granted('ROLE_SUPER_ADMIN')"),
        new GetCollection(security: "is_granted('ROLE_SUPER_ADMIN')"),
        new Post(security: "is_granted('ROLE_SUPER_ADMIN')"),
        new Patch(security: "is_granted('ROLE_SUPER_ADMIN')"),
        new Delete(security: "is_granted('ROLE_SUPER_ADMIN')"),
    ],
    normalizationContext: ['groups' => ['client:read']],
    denormalizationContext: ['groups' => ['client:write']],
    validationContext: ['groups' => ['Default', 'client:write']]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'name'     => 'ipartial',
    'isActive' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: [
    'name',
    'createdAt',
])]
#[ApiFilter(DateFilter::class, properties: ['createdAt'])]
class Client
{

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    #[Groups(['client:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique:true)]
    #[Groups(['client:read', 'client:write'])]
    #[ApiProperty(types: ['https://schema.org/name'])]
    private string $name;

    #[ORM\Column(length: 255)]
    #[Groups(['client:read', 'client:write'])]
    #[Assert\Url]
    private string $website;

    #[ORM\Column(length: 180)]
    #[Groups(['client:read', 'client:write'])]
    #[Assert\Email]
    private string $contactEmail;

    #[ORM\Column(length: 30)]
    #[Groups(['client:read', 'client:write'])]
    private string $contactPhone;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['client:read', 'client:write'])]
    private string $address;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['client:read', 'client:write'])]
    private ?\DateTimeImmutable $contractStart = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['client:read', 'client:write'])]
    private ?\DateTimeImmutable $contractEnd = null;

    #[ORM\Column(options: ['default' => true])]
    #[Groups(['client:read', 'client:write'])]
    #[ApiProperty(types: ['https://schema.org/active'])]
    private bool $isActive = true;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['client:read'])]
    #[ApiProperty(types: ['https://schema.org/dateCreated'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['client:read'])]
    #[ApiProperty(types: ['https://schema.org/dateModified'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(mappedBy: 'client', targetEntity: User::class, orphanRemoval: true, cascade: ['persist'])]
    #[MaxDepth(1)]
    #[Groups(['client:read'])]
    #[ApiProperty(readableLink: false, openapiContext: ['type' => 'array', 'items' => ['type' => 'string', 'format' => 'iri-reference']])]
    private Collection $users;


    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->contractStart = new \DateTimeImmutable();
        $this->users = new ArrayCollection();

    }


    public function getId(): ?int
    {
        return $this->id;

    }


    public function getName(): string
    {
        return $this->name;

    }


    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;

    }


    public function getWebsite(): string
    {
        return $this->website;

    }


    public function setWebsite(string $website): static
    {
        $this->website = $website;

        return $this;

    }


    public function getContactEmail(): string
    {
        return $this->contactEmail;

    }


    public function setContactEmail(string $contactEmail): static
    {
        $this->contactEmail = $contactEmail;

        return $this;

    }


    public function getContactPhone(): string
    {
        return $this->contactPhone;

    }


    public function setContactPhone(string $contactPhone): static
    {
        $this->contactPhone = $contactPhone;

        return $this;

    }


    public function getAddress(): string
    {
        return $this->address;

    }


    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;

    }


    public function getContractStart(): ?\DateTimeImmutable
    {
        return $this->contractStart;

    }


    public function setContractStart(?\DateTimeImmutable $contractStart): static
    {
        $this->contractStart = $contractStart;

        return $this;

    }


    public function getContractEnd(): ?\DateTimeImmutable
    {
        return $this->contractEnd;

    }


    public function setContractEnd(?\DateTimeImmutable $contractEnd): static
    {
        $this->contractEnd = $contractEnd;

        return $this;

    }


    public function isActive(): bool
    {
        return $this->isActive;

    }


    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;

    }


    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;

    }


    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;

    }


    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;

    }


    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;

    }


    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setClient($this);
        }

        return $this;

    }


    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            // Nothing else to do here: orphanRemoval takes care of it.
        }

        return $this;

    }


}
