<?php

namespace App\DataFixtures;

use App\Entity\Client;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

final class ClientFixtures extends Fixture
{
    public const REF_PRIMARY_CLIENT = 'primary-client';
    public const REF_OTHER_CLIENT = 'other-client';
    public const REF_CLIENT_PREFIX = 'client-';


    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Client principal
        $main = (new Client())
            ->setName('Acme Corp');
        $manager->persist($main);
        $this->addReference(self::REF_PRIMARY_CLIENT, $main);

        // Autre client pour tests cross-access
        $other = (new Client())
            ->setName('Globex');
        $manager->persist($other);
        $this->addReference(self::REF_OTHER_CLIENT, $other);

        // Client inactif pour les tests de filtre
        for ($i = 0; $i < 6; ++$i) {
            $inactive = (new Client())
                ->setName('Inactive '.$faker->unique()->company())
                ->setIsActive(false);
            $manager->persist($inactive);
            $this->addReference('inactive-client-'.$i, $inactive);
        }

        // 3 autres clients générés aléatoirement, référencés dynamiquement
        for ($i = 0; $i < 3; ++$i) {
            $client = (new Client())
                ->setName($faker->unique()->company());
            $manager->persist($client);
            $this->addReference(self::REF_CLIENT_PREFIX.$i, $client);
        }

        $manager->flush();

    }


}
