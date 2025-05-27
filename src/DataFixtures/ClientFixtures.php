<?php

namespace App\DataFixtures;

use App\Entity\Client;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

final class ClientFixtures extends Fixture
{
    public const REF_PRIMARY_CLIENT = 'primary-client';

    private Factory $faker;


    public function __construct(Factory $faker)
    {
        $this->faker = $faker;
    }


    /**
     * Loads client fixtures into the database.
     *
     * This method uses Faker to generate fake data and persists it into the database.
     * It is called automatically by the Doctrine fixtures loader.
     *
     * @param ObjectManager $manager The object manager for persisting entities
     *
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $faker = $this->faker->create('fr_FR');

        $main = (new Client())
            ->setName('Acme Corp');

        $manager->persist($main);
        $this->addReference(self::REF_PRIMARY_CLIENT, $main);

        for ($i = 0; $i < 3; ++$i) {
            $manager->persist(
                (new Client())
                    ->setName($faker->company())
            );
        }

        $manager->flush();

    }


}
