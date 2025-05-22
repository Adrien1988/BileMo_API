<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'product')]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
    ],
    normalizationContext: ['groups' => ['product:read']],
    denormalizationContext: ['groups' => ['product:write']]
)]
class Product
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    #[Groups(['product:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['product:read', 'product:write'])]
    private string $name;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['product:read', 'product:write'])]
    private string $description;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['product:read', 'product:write'])]
    private string $price;

    #[ORM\Column(length: 255)]
    #[Groups(['product:read', 'product:write'])]
    private string $brand;

    #[ORM\Column(length: 255)]
    #[Groups(['product:read', 'product:write'])]
    private string $imageUrl;


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


}
