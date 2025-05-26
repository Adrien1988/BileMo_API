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


    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Produit vitrine
        $manager->persist(
            (new Product())
                ->setName('BileMo X1')
                ->setDescription('Smartphone haut de gamme, écran OLED 6,5 ’’')
                ->setPrice('899.00')
                ->setBrand('BileMo')
                ->setImageUrl('https://picsum.photos/seed/bilemo-x1/640/480')
        );

        // Smartphones aléatoires
        for ($i = 0; $i < self::EXTRA_PRODUCTS; ++$i) {
            // 1) tire une marque au hasard
            $brand = $faker->randomKey(self::PHONES);

            // 2) puis un modèle dans la liste associée
            $model = $faker->randomElement(self::PHONES[$brand]);

            // 3) éventuellement ajoute un suffixe « 5G » / « Pro » / etc.
            if ($faker->boolean(30) && !str_contains($model, 'Pro')) {
                $model .= ' '.$faker->randomElement(['5G', 'Pro', 'Ultra']);
            }

            $manager->persist(
                (new Product())
                    ->setName("$brand $model")
                    ->setDescription($faker->sentence(12))
                    ->setPrice(number_format($faker->randomFloat(2, 199, 1599), 2, '.', ''))
                    ->setBrand($brand)
                    ->setImageUrl("https://picsum.photos/seed/{$faker->uuid}/640/480")
            );
        }

        $manager->flush();
    }


}
