<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Post;
use Doctrine\DBAL\Types\Types;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ClientRepository;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

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
class Client
{

    /**
     * The unique identifier of the client.
     *
     * @var int|null
     */
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    #[Groups(['client:read'])]
    private ?int $id = null;

    /**
     * Human‑readable name of the client.
     *
     * @var string
     */
    #[ORM\Column(length: 255, unique:true)]
    #[Groups(['client:read', 'client:write'])]
    private string $name;

    /**
     * Whether the client is active.
     *
     * @var bool
     */
    #[ORM\Column(options: ['default' => true])]
    #[Groups(['client:read', 'client:write'])]
    private bool $isActive = true;

    /**
     * Timestamp when the client was created.
     *
     * @var \DateTimeImmutable
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['client:read'])]
    private \DateTimeImmutable $createdAt;

    /**
     * Timestamp when the client was last updated.
     *
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['client:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * Users associated with the client.
     *
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(mappedBy: 'client', targetEntity: User::class, orphanRemoval: true, cascade: ['persist'])]
    #[Groups(['client:read'])]
    private Collection $users;


    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
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
