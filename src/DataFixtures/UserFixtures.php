<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;   // <- BON namespace
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public const REF_API_USER = 'api-user';


    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
    ) {
    }


    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        /** @var Client $client référence définie dans ClientFixtures */
        $client = $this->getReference(ClientFixtures::REF_PRIMARY_CLIENT, Client::class);

        /* ---------- Utilisateur principal JWT ------------------------ */
        $apiUser = new User();
        $apiUser
            ->setFirstName('Jane')
            ->setLastName('Doe')
            ->setEmail('api@example.com')
            ->setPassword(
                $this->hasher->hashPassword($apiUser, 'secret')
            )
            ->setClient($client);

        $manager->persist($apiUser);
        $this->addReference(self::REF_API_USER, $apiUser);

        /* ---------- 5 utilisateurs aléatoires ----------------------- */
        for ($i = 0; $i < 5; ++$i) {
            $user = new User();
            $user
                ->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setEmail($faker->unique()->safeEmail())
                ->setPassword(
                    $this->hasher->hashPassword($user, 'password')
                )
                ->setClient($client);

            $manager->persist($user);
        }

        $manager->flush();
    }


    /** @return array<class-string> */
    public function getDependencies(): array
    {
        return [ClientFixtures::class];
    }


}
