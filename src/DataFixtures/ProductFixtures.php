<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

final class ProductFixtures extends Fixture
{


    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        /* ---------- Un produit phare pour les tests ------------------- */
        $manager->persist(
            (new Product())
                ->setName('BileMo X1')
                ->setDescription('Smartphone haut de gamme, écran OLED 6,5’')
                ->setPrice('899.00')                               // string
                ->setBrand('BileMo')
                ->setImageUrl('https://picsum.photos/seed/bilemo-x1/640/480')
        );

        /* ---------- 10 produits aléatoires (optionnel) ---------------- */
        for ($i = 0; $i < 10; ++$i) {
            $manager->persist(
                (new Product())
                    ->setName($faker->unique()->words(3, true))
                    ->setDescription($faker->sentence(12))
                    ->setPrice(
                        // PHPStan veut un string, on convertit proprement
                        number_format($faker->randomFloat(2, 199, 1299), 2, '.', '')
                    )
                    ->setBrand($faker->company())
                    ->setImageUrl($faker->imageUrl(640, 480, 'technics', true))
            );
        }

        $manager->flush();
    }


}
