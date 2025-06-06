<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Enum\UserRole;
use App\Repository\UserRepository;
use App\State\UserCreationProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(
    name: 'uniq_email_per_client',
    columns: ['client_id', 'email']
)]
#[UniqueEntity(fields: ['email', 'client'], message: 'This email is already used for this client.')]
#[ApiResource(
    operations: [
        new Get(security: "is_granted('ROLE_SUPER_ADMIN') or (is_granted('ROLE_ADMIN')  and object.getClient() == user.getClient())"),
        new Put(security: "is_granted('ROLE_SUPER_ADMIN') or (is_granted('ROLE_ADMIN')  and object.getClient() == user.getClient())"),
        new Delete(security: "is_granted('ROLE_SUPER_ADMIN') or (is_granted('ROLE_ADMIN')  and object.getClient() == user.getClient())"),
        new GetCollection(security: "is_granted('ROLE_SUPER_ADMIN')"),
        new Post(security: "is_granted('ROLE_SUPER_ADMIN') or is_granted('ROLE_ADMIN')", processor: UserCreationProcessor::class, denormalizationContext: ['groups' => ['user:write', 'user:write:superadmin']]
        ),
    ],
    normalizationContext: ['groups' => ['user:read']],

)]
#[ApiResource(
    uriTemplate: '/clients/{id}/users',
    uriVariables: [
        'id' => new Link(fromClass: Client::class, fromProperty: 'users'),
    ],
    operations: [
        new GetCollection(
            security: "
            is_granted('ROLE_SUPER_ADMIN') or (
            is_granted('ROLE_ADMIN') 
            and user.getClient() 
            and user.getClient().getId() == request.attributes.get('id')
            )
            "
        ),
    ],
    normalizationContext: ['groups' => ['user:read']]
)]
/* ---------- Filtres applicables à TOUTES les routes ---------- */
#[ApiFilter(SearchFilter::class, properties: [
    'email'     => 'exact',
    'firstName' => 'ipartial',
    'lastName'  => 'ipartial',
])]
#[ApiFilter(DateFilter::class, properties: ['createdAt'])]
#[ApiFilter(OrderFilter::class, properties: [
    'firstName',
    'lastName',
    'email',
    'createdAt',
])]
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
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['user:read', 'user:write', 'user:write:superadmin'])]
    private ?Client $client = null;


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


    public function getClient(): ?Client
    {
        return $this->client;

    }


    public function setClient(?Client $client): static
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

    }


}
