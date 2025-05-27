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

        $client = (new Client())
            ->setName('ACME')
            ->setIsActive(false);

        self::assertSame('ACME', $client->getName());
        self::assertFalse($client->isActive());

        $createdAt = $client->getCreatedAt();
        self::assertInstanceOf(\DateTimeImmutable::class, $createdAt);

        self::assertNull($client->getUpdatedAt());

        $now = new \DateTimeImmutable();
        $client->setUpdatedAt($now);
        self::assertSame($now, $client->getUpdatedAt());

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
