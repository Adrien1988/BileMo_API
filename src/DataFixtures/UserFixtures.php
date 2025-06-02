<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public const REF_API_USER = 'api-user';
    public const REF_OTHER_CLIENT_USER = 'other-client-user';


    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
    ) {

    }


    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // @var Client $client

        $client = $this->getReference(ClientFixtures::REF_PRIMARY_CLIENT, Client::class);
        // @var Client $otherClient

        $otherClient = $this->getReference(ClientFixtures::REF_OTHER_CLIENT, Client::class);

        // ----- SUPER ADMIN -----
        $superAdmin = new User();
        $superAdmin
            ->setFirstName('Super')
            ->setLastName('Admin')
            ->setEmail('superadmin@example.com')
            ->setPassword($this->hasher->hashPassword($superAdmin, 'supersecret'))
            ->setRole(UserRole::ROLE_SUPER_ADMIN)
            ->setClient(null);
        $manager->persist($superAdmin);

        // ----- ADMIN PRINCIPAL -----
        $clientAdmin = new User();
        $clientAdmin
            ->setFirstName('Alice')
            ->setLastName('Admin')
            ->setEmail('admin@acme.com')
            ->setPassword($this->hasher->hashPassword($clientAdmin, 'adminsecret'))
            ->setRole(UserRole::ROLE_ADMIN)
            ->setClient($client);
        $manager->persist($clientAdmin);

        // ----- ADMIN AUTRE CLIENT -----
        $otherClientAdmin = new User();
        $otherClientAdmin
            ->setFirstName('Bob')
            ->setLastName('Admin')
            ->setEmail('admin@other.com')
            ->setPassword($this->hasher->hashPassword($otherClientAdmin, 'adminsecret'))
            ->setRole(UserRole::ROLE_ADMIN)
            ->setClient($otherClient);
        $manager->persist($otherClientAdmin);

        // ----- Utilisateur API par défaut (ROLE_USER) -----
        $apiUser = new User();
        $apiUser
            ->setFirstName('Jane')
            ->setLastName('Doe')
            ->setEmail('api@example.com')
            ->setPassword($this->hasher->hashPassword($apiUser, 'secret'))
            ->setRole(UserRole::ROLE_USER)
            ->setClient($client);
        $manager->persist($apiUser);
        $this->addReference(self::REF_API_USER, $apiUser);

        // ----- Users standards pour le client principal -----
        for ($i = 0; $i < 5; ++$i) {
            $user = new User();
            $user
                ->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setEmail($faker->unique()->safeEmail())
                ->setPassword($this->hasher->hashPassword($user, 'password'))
                ->setRole(UserRole::ROLE_USER)
                ->setClient($client);
            $manager->persist($user);
        }

        // ----- Utilisateur pour l'autre client (pour tests d'accès interdit) -----
        $otherUser = new User();
        $otherUser
            ->setFirstName('Paul')
            ->setLastName('Smith')
            ->setEmail('paul.smith@example.com')
            ->setPassword($this->hasher->hashPassword($otherUser, 'secret'))
            ->setRole(UserRole::ROLE_USER)
            ->setClient($otherClient);
        $manager->persist($otherUser);
        $this->addReference(self::REF_OTHER_CLIENT_USER, $otherUser);

        // ----- Users standards pour l'autre client -----
        for ($i = 0; $i < 3; ++$i) {
            $user = new User();
            $user
                ->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setEmail($faker->unique()->safeEmail())
                ->setPassword($this->hasher->hashPassword($user, 'password'))
                ->setRole(UserRole::ROLE_USER)
                ->setClient($otherClient);
            $manager->persist($user);
        }

        $manager->flush();

    }


    public function getDependencies(): array
    {
        return [ClientFixtures::class];

    }


}
