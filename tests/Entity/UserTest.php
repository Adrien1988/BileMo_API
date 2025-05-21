<?php

namespace App\Tests\Entity;

use App\Entity\Client;
use App\Entity\User;
use App\Enum\UserRole;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testAllGettersAndSetters(): void
    {
        $client = (new Client())->setName('Globex');

        $user = (new User())
            ->setFirstName('Bob')
            ->setLastName('Jones')
            ->setEmail('bob@globex.com')
            ->setPassword('hash123')
            ->setRole(UserRole::ROLE_ADMIN)
            ->setIsActive(false)
            ->setClient($client);

        // ─── Getters / setters de base ──────────────────────────────
        self::assertSame('Bob', $user->getFirstName());
        self::assertSame('Jones', $user->getLastName());
        self::assertSame('bob@globex.com', $user->getEmail());
        self::assertSame('hash123', $user->getPassword());
        self::assertSame(UserRole::ROLE_ADMIN, $user->getRole());
        self::assertFalse($user->isActive());
        self::assertSame($client, $user->getClient());

        // ─── Couverture des dates ──────────────────────────────────
        $createdAt = $user->getCreatedAt();
        self::assertInstanceOf(\DateTimeImmutable::class, $createdAt);

        // updatedAt débute à null
        self::assertNull($user->getUpdatedAt());

        // on le modifie puis on vérifie
        $now = new \DateTimeImmutable();
        $user->setUpdatedAt($now);
        self::assertSame($now, $user->getUpdatedAt());
    }
}
