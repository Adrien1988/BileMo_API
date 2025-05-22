<?php

namespace App\Tests\Entity;

use App\Entity\Product;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{


    public function testGettersAndSetters(): void
    {
        $product = (new Product())
            ->setName('Galaxy S25')
            ->setDescription('Flagship Samsung')
            ->setPrice('999.90')
            ->setBrand('Samsung')
            ->setImageUrl('https://example.com/galaxy_s25.png');

        self::assertSame('Galaxy S25', $product->getName());
        self::assertSame('Flagship Samsung', $product->getDescription());
        self::assertSame('999.90', $product->getPrice());
        self::assertSame('Samsung', $product->getBrand());
        self::assertSame('https://example.com/galaxy_s25.png', $product->getImageUrl());
    }


}
