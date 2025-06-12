<?php

namespace App\Tests\Entity;

use App\Entity\Client;
use App\Entity\User;
use App\Enum\UserRole;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{


    public function testFullEntityBehaviour(): void
    {
        /* --------- Instanciation et setters --------- */
        $contractStart = new \DateTimeImmutable('2024-01-01');
        $contractEnd = new \DateTimeImmutable('2026-01-01');

        $client = (new Client())
            ->setName('ACME')
            ->setIsActive(false)
            ->setWebsite('https://acme.example')
            ->setContactEmail('contact@acme.example')
            ->setContactPhone('+33123456789')
            ->setAddress("1 rue de la Paix\n75002 Paris")
            ->setContractStart($contractStart)
            ->setContractEnd($contractEnd);

        /* --------- Asserts sur les nouveaux champs --------- */
        self::assertSame('https://acme.example', $client->getWebsite());
        self::assertSame('contact@acme.example', $client->getContactEmail());
        self::assertSame('+33123456789', $client->getContactPhone());
        self::assertSame("1 rue de la Paix\n75002 Paris", $client->getAddress());
        self::assertSame($contractStart, $client->getContractStart());
        self::assertSame($contractEnd, $client->getContractEnd());

        /* --------- Asserts pré-existants --------- */
        self::assertSame('ACME', $client->getName());
        self::assertFalse($client->isActive());

        $createdAt = $client->getCreatedAt();
        self::assertInstanceOf(\DateTimeImmutable::class, $createdAt);

        self::assertNull($client->getUpdatedAt());

        $now = new \DateTimeImmutable();
        $client->setUpdatedAt($now);
        self::assertSame($now, $client->getUpdatedAt());

        /* --------- Gestion de la relation avec User --------- */
        $user = (new User())
            ->setFirstName('Alice')
            ->setLastName('Smith')
            ->setEmail('alice@acme.com')
            ->setPassword('hash')
            ->setRole(UserRole::ROLE_USER)
            ->setClient($client);

        $client->addUser($user);
        self::assertCount(1, $client->getUsers());
        self::assertSame($client, $user->getClient());

        $client->removeUser($user);
        self::assertCount(0, $client->getUsers());

    }


}
