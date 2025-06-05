<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\DataFixtures\ClientFixtures;
use App\DataFixtures\UserFixtures;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;

/**
 * Vérifie la création d’utilisateurs :
 *  – ROLE_ADMIN → uniquement pour son client
 *  – ROLE_SUPER_ADMIN → pour n’importe quel client
 */
final class UserCreateTest extends ApiTestCase
{
    use JwtAuthenticatedUserTrait;

    /**
     * @var \Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool
     */
    private $databaseTool;


    protected function setUp(): void
    {
        parent::setUp();

        $this->databaseTool = self::getContainer()
            ->get(DatabaseToolCollection::class)
            ->get();

        // chargement minimal : clients + users
        $this->databaseTool->loadFixtures([
            ClientFixtures::class,
            UserFixtures::class,
        ]);

    }


    public function testAdminCanCreateUserForOwnClient(): void
    {
        $client = $this->createAuthenticatedUserClient('admin@acme.com', 'adminsecret');

        $client->request('POST', '/api/users', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json'    => [
                'email'     => 'newuser@acme.com',
                'password'  => 'SecurePass!23',
                'firstName' => 'Jean',
                'lastName'  => 'Dupont',
                'role'      => 'ROLE_USER',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            'email' => 'newuser@acme.com',
        ]);

    }


    public function testSuperAdminCanCreateUserForAnyClient(): void
    {
        $client = $this->createAuthenticatedUserClient('superadmin@example.com', 'supersecret');
        $clientIri = '/api/clients/1';

        $client->request('POST', '/api/users', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json'    => [
                'email'     => 'anyclient@admin.com',
                'password'  => 'SecurePass!45',
                'firstName' => 'Chloé',
                'lastName'  => 'Martin',
                'role'      => 'ROLE_ADMIN',
                'client'    => $clientIri,
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            'email' => 'anyclient@admin.com',
        ]);

    }


    public function testSuperAdminMustProvideClient(): void
    {
        $client = $this->createAuthenticatedUserClient('superadmin@example.com', 'supersecret');

        $client->request('POST', '/api/users', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json'    => [
                'email'     => 'noclient@admin.com',
                'password'  => 'SecurePass!89',
                'firstName' => 'Sam',
                'lastName'  => 'SansClient',
                'role'      => 'ROLE_USER',
                // pas de champ "client"
            ],
        ]);

        $this->assertResponseStatusCodeSame(400);

    }


    public function testAdminCannotCreateUserForAnotherClient(): void
    {
        $client = $this->createAuthenticatedUserClient('admin@acme.com', 'adminsecret');
        $otherClientIri = '/api/clients/2';

        $client->request('POST', '/api/users', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json'    => [
                'email'     => 'wrongclient@fail.com',
                'password'  => 'SecurePass!77',
                'firstName' => 'Evil',
                'lastName'  => 'Admin',
                'role'      => 'ROLE_USER',
                'client'    => $otherClientIri,
            ],
        ]);

        $this->assertResponseStatusCodeSame(403);

    }


    public function testCannotCreateDuplicateEmailForSameClient(): void
    {
        $client = $this->createAuthenticatedUserClient('admin@acme.com', 'adminsecret');

        // 1️⃣  Première création — doit réussir

        $client->request('POST', '/api/users', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json'    => [
                'email'     => 'dup@acme.com',
                'password'  => 'SecurePass!99',
                'firstName' => 'First',
                'lastName'  => 'Try',
                'role'      => 'ROLE_USER',
            ],
        ]);
        $this->assertResponseStatusCodeSame(201);

        // 2️⃣  Tentative de doublon — doit échouer

        $client->request('POST', '/api/users', [
            'headers' => ['Content-Type' => 'application/ld+json'],
            'json'    => [
                'email'     => 'dup@acme.com',
                'password'  => 'SecurePass!99',
                'firstName' => 'Second',
                'lastName'  => 'Try',
                'role'      => 'ROLE_USER',
            ],
        ]);
        $this->assertResponseStatusCodeSame(400);

    }


}
