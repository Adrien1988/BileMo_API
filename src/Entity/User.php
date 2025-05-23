<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Enum\UserRole;
use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(
    name: '`user`',
    uniqueConstraints: [new ORM\UniqueConstraint(name: 'uniq_email_per_client', columns: ['client_id', 'email'])]
)]
#[UniqueEntity(fields: ['email', 'client'], message: 'This email is already used for this client.')]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'user:write'])]
    private string $firstName;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'user:write'])]
    private string $lastName;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'user:write'])]
    private string $email;

    #[ORM\Column(length: 255)]
    #[Groups(['user:write'])]
    private string $password;

    #[ORM\Column(enumType: UserRole::class)]
    #[Groups(['user:read', 'user:write'])]
    private UserRole $role = UserRole::ROLE_USER;

    #[ORM\Column(options: ['default' => true])]
    #[Groups(['user:read', 'user:write'])]
    private bool $isActive = true;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['user:read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['user:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['user:read', 'user:write'])]
    private Client $client;


    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }


    public function getId(): ?int
    {
        return $this->id;
    }


    public function getFirstName(): string
    {
        return $this->firstName;
    }


    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }


    public function getLastName(): string
    {
        return $this->lastName;
    }


    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }


    public function getEmail(): string
    {
        return $this->email;
    }


    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }


    public function getPassword(): string
    {
        return $this->password;
    }


    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }


    public function getRole(): UserRole
    {
        return $this->role;
    }


    public function setRole(UserRole $role): static
    {
        $this->role = $role;

        return $this;
    }


    public function getRoles(): array
    {
        // On tire profit de ta méthode existante
        return [$this->getRole()->value];
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


    public function getClient(): Client
    {
        return $this->client;
    }


    public function setClient(Client $client): static
    {
        $this->client = $client;

        return $this;
    }


    public function getUserIdentifier(): string
    {
        return $this->email;
    }


    public function eraseCredentials(): void
    {
        // Laisse vide si tu n’as pas de données sensibles temporaires
    }


}
