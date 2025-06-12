<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'product')]
#[ApiResource(
    paginationClientItemsPerPage: true,
    paginationItemsPerPage: 30,
    operations: [
        new Get(security: "is_granted('IS_AUTHENTICATED_FULLY')"),
        new GetCollection(security: "is_granted('IS_AUTHENTICATED_FULLY')"),
        new Post(security: "is_granted('ROLE_SUPER_ADMIN')", securityMessage: 'Réservé aux super-admins.'),
        new Patch(security: "is_granted('ROLE_SUPER_ADMIN')"),
        new Delete(security: "is_granted('ROLE_SUPER_ADMIN')"),
    ],
    normalizationContext: ['groups' => ['product:read']],
    denormalizationContext: ['groups' => ['product:write']]
)]
#[ApiFilter(
    OrderFilter::class,
    properties: [
        'name',
        'price',
    ],
    arguments: [
        'orderParameterName' => 'order',
    ]
)]
#[ApiFilter(RangeFilter::class, properties: ['price'])]
#[ApiFilter(SearchFilter::class, properties: [
    'name'  => 'ipartial',
    'brand' => 'ipartial',
])]
class Product
{

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    #[Groups(['product:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['product:read', 'product:write'])]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[ApiProperty(types: ['https://schema.org/name'])]
    private string $name;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['product:read', 'product:write'])]
    #[ApiProperty(types: ['https://schema.org/description'])]
    private string $description;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['product:read', 'product:write'])]
    #[Assert\Positive(message: 'Le prix doit être strictement positif.')]
    #[ApiProperty(types: ['https://schema.org/price'])]
    private string $price;

    #[ORM\Column(length: 255)]
    #[Groups(['product:read', 'product:write'])]
    #[ApiProperty(types: ['https://schema.org/brand'])]
    private string $brand;

    #[ORM\Column(length: 255)]
    #[Groups(['product:read', 'product:write'])]
    #[ApiProperty(types: ['https://schema.org/image'])]
    private string $imageUrl;

    #[ORM\Column(length: 50)]
    #[Groups(['product:read', 'product:write'])]
    #[ApiProperty(types: ['https://schema.org/color'])]
    private string $color;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['product:read', 'product:write'])]
    #[Assert\Positive]
    private int $storageCapacity;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['product:read', 'product:write'])]
    #[Assert\Positive]
    private int $ram;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 1)]
    #[Groups(['product:read', 'product:write'])]
    #[Assert\Positive]
    private string $screenSize;

    #[ORM\Column(length: 20)]
    #[Groups(['product:read', 'product:write'])]
    private string $cameraResolution;

    #[ORM\Column(length: 30)]
    #[Groups(['product:read', 'product:write'])]
    private string $operatingSystem;

    #[ORM\Column(length: 20)]
    #[Groups(['product:read', 'product:write'])]
    private string $batteryCapacity;


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


    public function getDescription(): string
    {
        return $this->description;

    }


    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;

    }


    public function getPrice(): string
    {
        return $this->price;

    }


    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;

    }


    public function getBrand(): string
    {
        return $this->brand;

    }


    public function setBrand(string $brand): static
    {
        $this->brand = $brand;

        return $this;

    }


    public function getImageUrl(): string
    {
        return $this->imageUrl;

    }


    public function setImageUrl(string $imageUrl): static
    {
        $this->imageUrl = $imageUrl;

        return $this;

    }


    public function getColor(): string
    {
        return $this->color;

    }


    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;

    }


    public function getStorageCapacity(): int
    {
        return $this->storageCapacity;

    }


    public function setStorageCapacity(int $storageCapacity): static
    {
        $this->storageCapacity = $storageCapacity;

        return $this;

    }


    public function getRam(): int
    {
        return $this->ram;

    }


    public function setRam(int $ram): static
    {
        $this->ram = $ram;

        return $this;

    }


    public function getScreenSize(): string
    {
        return $this->screenSize;

    }


    public function setScreenSize(string $screenSize): static
    {
        $this->screenSize = $screenSize;

        return $this;

    }


    public function getCameraResolution(): string
    {
        return $this->cameraResolution;

    }


    public function setCameraResolution(string $cameraResolution): static
    {
        $this->cameraResolution = $cameraResolution;

        return $this;

    }


    public function getOperatingSystem(): string
    {
        return $this->operatingSystem;

    }


    public function setOperatingSystem(string $operatingSystem): static
    {
        $this->operatingSystem = $operatingSystem;

        return $this;

    }


    public function getBatteryCapacity(): string
    {
        return $this->batteryCapacity;

    }


    public function setBatteryCapacity(string $batteryCapacity): static
    {
        $this->batteryCapacity = $batteryCapacity;

        return $this;

    }


}
