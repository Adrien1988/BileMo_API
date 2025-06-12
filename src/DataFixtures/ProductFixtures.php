<?php

// src/DataFixtures/ProductFixtures.php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

final class ProductFixtures extends Fixture
{
    private const EXTRA_PRODUCTS = 149;

    /** @var array<string,array<string>> */
    private const PHONES = [
        'Apple'   => ['iPhone 14', 'iPhone 14 Pro', 'iPhone 14 Pro Max', 'iPhone 15', 'iPhone 15 Pro', 'iPhone SE'],
        'Samsung' => ['Galaxy S24', 'Galaxy S24+', 'Galaxy S24 Ultra', 'Galaxy Z Flip5', 'Galaxy A55'],
        'Google'  => ['Pixel 8', 'Pixel 8 Pro', 'Pixel 7a'],
        'Xiaomi'  => ['Redmi Note 13', 'Redmi Note 13 Pro', 'Xiaomi 14', 'Poco F6'],
        'OnePlus' => ['12', '12R', 'Nord CE 4'],
        'Honor'   => ['Magic6 Pro', '90', '90 Lite'],
        'Nothing' => ['Phone (2a)', 'Phone (2)'],
    ];

    /** @var string[] */
    private const COLORS = ['Black', 'White', 'Blue', 'Graphite', 'Green', 'Red', 'Purple', 'Silver'];

    /** @var string[] */
    private const CAMERA_RESOLUTIONS = ['12 MP', '48 MP', '50 MP', '64 MP', '108 MP', '200 MP'];

    /** @var string[] */
    private const ANDROID_VERSIONS = ['Android 12', 'Android 13', 'Android 14'];
    /** @var string[] */
    private const IOS_VERSIONS = ['iOS 16', 'iOS 17'];


    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Produit vitrine BileMo
        $manager->persist(
            (new Product())
                ->setName('BileMo X1')
                ->setDescription('Smartphone haut de gamme, écran OLED 6,5’’')
                ->setPrice('899.00')
                ->setBrand('BileMo')
                ->setImageUrl('https://picsum.photos/seed/bilemo-x1/640/480')
                ->setColor('Graphite')
                ->setStorageCapacity(256)
                ->setRam(8)
                ->setScreenSize('6.5')
                ->setCameraResolution('48 MP')
                ->setOperatingSystem('Android 14')
                ->setBatteryCapacity('5000 mAh')
        );

        // Génération aléatoire des autres produits
        for ($i = 0; $i < self::EXTRA_PRODUCTS; ++$i) {
            $brand = $faker->randomKey(self::PHONES);
            $model = $faker->randomElement(self::PHONES[$brand]);

            if ($faker->boolean(30) && !str_contains($model, 'Pro')) {
                $model .= ' '.$faker->randomElement(['5G', 'Pro', 'Ultra']);
            }

            $isApple = $brand === 'Apple';

            $product = (new Product())
                ->setName("$brand $model")
                ->setDescription($faker->sentence(12))
                ->setPrice(number_format($faker->randomFloat(2, 199, 1599), 2, '.', ''))
                ->setBrand($brand)
                ->setImageUrl("https://picsum.photos/seed/{$faker->uuid}/640/480")
                ->setColor($faker->randomElement(self::COLORS))
                ->setStorageCapacity($faker->randomElement([64, 128, 256, 512, 1024]))
                ->setRam($faker->randomElement([4, 6, 8, 12, 16]))
                ->setScreenSize((string) $faker->randomFloat(1, 5.5, 7.1))
                ->setCameraResolution($faker->randomElement(self::CAMERA_RESOLUTIONS))
                ->setOperatingSystem(
                    $isApple ? $faker->randomElement(self::IOS_VERSIONS) : $faker->randomElement(self::ANDROID_VERSIONS)
                )
                ->setBatteryCapacity($faker->numberBetween(3000, 6000).' mAh');

            $manager->persist($product);
        }

        $manager->flush();

    }


}
