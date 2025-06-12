<?php

// src/DataFixtures/ClientFixtures.php

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

        /* ---------- Primary client ---------- */
        $main = (new Client())
            ->setName('Acme Corp')
            ->setWebsite('https://acme-corp.example')
            ->setContactEmail('contact@acme-corp.example')
            ->setContactPhone('+33123456789')
            ->setAddress("1 rue de l'Innovation\n75002 Paris")
            ->setContractStart(new \DateTimeImmutable('-1 year'));
        $manager->persist($main);
        $this->addReference(self::REF_PRIMARY_CLIENT, $main);

        /* ---------- Secondary client (cross-access tests) ---------- */
        $other = (new Client())
            ->setName('Globex')
            ->setWebsite('https://globex.example')
            ->setContactEmail('contact@globex.example')
            ->setContactPhone('+33198765432')
            ->setAddress("99 avenue du Progrès\n69007 Lyon")
            ->setContractStart(new \DateTimeImmutable('-6 months'));
        $manager->persist($other);
        $this->addReference(self::REF_OTHER_CLIENT, $other);

        /* ---------- Inactive clients ---------- */
        for ($i = 0; $i < 6; ++$i) {
            $inactive = (new Client())
                ->setName('Inactive '.$faker->unique()->company())
                ->setIsActive(false)
                ->setWebsite('https://'.$faker->domainName())
                ->setContactEmail($faker->companyEmail())
                ->setContactPhone($faker->phoneNumber())
                ->setAddress($faker->address())
                ->setContractStart(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-3 years', '-1 year')))
                ->setContractEnd(null);
            $manager->persist($inactive);
            $this->addReference('inactive-client-'.$i, $inactive);
        }

        /* ---------- Three random active clients ---------- */
        for ($i = 0; $i < 3; ++$i) {
            $startDate = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-2 years', 'now'));
            $endDate = $faker->boolean(40) ? \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('now', '+2 years')) : null;

            $client = (new Client())
                ->setName($faker->unique()->company())
                ->setWebsite('https://'.$faker->domainName())
                ->setContactEmail($faker->companyEmail())
                ->setContactPhone($faker->phoneNumber())
                ->setAddress($faker->address())
                ->setContractStart($startDate)
                ->setContractEnd($endDate);
            $manager->persist($client);
            $this->addReference(self::REF_CLIENT_PREFIX.$i, $client);
        }

        $manager->flush();

    }


}
