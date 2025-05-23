<?php

namespace App\DataFixtures;

use App\Entity\Client;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

final class ClientFixtures extends Fixture
{
    public const REF_PRIMARY_CLIENT = 'primary-client';


    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        /* ---------- Client principal (pour les tests) ---------------- */
        $main = (new Client())
            ->setName('Acme Corp');

        $manager->persist($main);
        $this->addReference(self::REF_PRIMARY_CLIENT, $main);

        /* ---------- Quelques autres clients aléatoires (optionnel) --- */
        for ($i = 0; $i < 3; ++$i) {
            $manager->persist(
                (new Client())
                    ->setName($faker->company())
            );
        }

        $manager->flush();
    }


}
