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
            ->setImageUrl('https://example.com/galaxy_s25.png')
            ->setColor('Blue')
            ->setStorageCapacity(512)
            ->setRam(12)
            ->setScreenSize('6.8')
            ->setCameraResolution('200 MP')
            ->setOperatingSystem('Android 15')
            ->setBatteryCapacity('5500 mAh');

        self::assertSame('Galaxy S25', $product->getName());
        self::assertSame('Flagship Samsung', $product->getDescription());
        self::assertSame('999.90', $product->getPrice());
        self::assertSame('Samsung', $product->getBrand());
        self::assertSame('https://example.com/galaxy_s25.png', $product->getImageUrl());

        self::assertSame('Blue', $product->getColor());
        self::assertSame(512, $product->getStorageCapacity());
        self::assertSame(12, $product->getRam());
        self::assertSame('6.8', $product->getScreenSize());
        self::assertSame('200 MP', $product->getCameraResolution());
        self::assertSame('Android 15', $product->getOperatingSystem());
        self::assertSame('5500 mAh', $product->getBatteryCapacity());

    }


}
