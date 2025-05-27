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
        // ── Construction & setters de base ───────────────────────────────
        $client = (new Client())
            ->setName('ACME')
            ->setIsActive(false);                       // nous allons vérifier isActive()

        self::assertSame('ACME', $client->getName());
        self::assertFalse($client->isActive());

        // ── Dates ────────────────────────────────────────────────────────
        $createdAt = $client->getCreatedAt();
        self::assertInstanceOf(\DateTimeImmutable::class, $createdAt);

        // updatedAt d’origine = null
        self::assertNull($client->getUpdatedAt());

        // on définit updatedAt et on vérifie
        $now = new \DateTimeImmutable();
        $client->setUpdatedAt($now);
        self::assertSame($now, $client->getUpdatedAt());

        // ── Collection users (add / remove) ─────────────────────────────
        $user = (new User())
            ->setFirstName('Alice')
            ->setLastName('Smith')
            ->setEmail('alice@acme.com')
            ->setPassword('hash')
            ->setRole(UserRole::ROLE_USER)
            ->setClient($client);

        // addUser synchronise les deux côtés
        $client->addUser($user);
        self::assertCount(1, $client->getUsers());
        self::assertSame($client, $user->getClient());

        // removeUser détache correctement
        $client->removeUser($user);
        self::assertCount(0, $client->getUsers());

    }


}
