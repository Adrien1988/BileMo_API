<?php
// tests/Functional/ClientAdminResourceTest.php

namespace App\Tests\Functional;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\DataFixtures\{ClientFixtures, UserFixtures};
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;

class ClientAdminResourceTest extends ApiTestCase
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

        // Charge les mêmes fixtures que le reste de la suite
        $this->databaseTool->loadFixtures([
            ClientFixtures::class,
            UserFixtures::class,
        ]);
    }


    /** @test */
    public function superAdminCanCrudClient(): void
    {
        // Super-admin défini dans UserFixtures
        $client  = $this->createAuthenticatedUserClient('superadmin@example.com');
        $headers = [
            'Content-Type' => 'application/ld+json',
            'Accept'       => 'application/ld+json',
        ];

        /* --- CREATE --- */
        $response = $client->request('POST', '/api/clients', [
            'headers' => $headers,
            'json'    => ['name' => 'Partner Z'],
        ]);
        $this->assertResponseStatusCodeSame(201);
        $iri = $response->toArray()['@id'];

        /* --- UPDATE --- */
        $client->request('PATCH', $iri, [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
                'Accept'       => 'application/ld+json',
            ],
            'json'    => ['name' => 'Partner Z - Updated'],
        ]);
        $this->assertResponseStatusCodeSame(200);

        /* --- DELETE --- */
        $client->request('DELETE', $iri);
        $this->assertResponseStatusCodeSame(204);
    }


    /** @test */
    public function otherRolesGet403OnWrite(): void
    {
        $client  = $this->createAuthenticatedUserClient('api@example.com');
        $headers = [
            'Content-Type' => 'application/ld+json',
            'Accept'       => 'application/ld+json',
        ];

        $client->request('POST', '/api/clients', [
            'headers' => $headers,
            'json'    => ['name' => 'Forbidden Corp'],
        ]);
        $this->assertResponseStatusCodeSame(403);
    }


    /** @test */
    public function duplicateNameReturns422(): void
    {
        // « Client A » est déjà créé par ClientFixtures
        $client  = $this->createAuthenticatedUserClient('superadmin@example.com');
        $headers = [
            'Content-Type' => 'application/ld+json',
            'Accept'       => 'application/ld+json',
        ];

        $client->request('POST', '/api/clients', [
            'headers' => $headers,
            'json'    => ['name' => 'Acme Corp'],
        ]);
        $this->assertResponseStatusCodeSame(422);
    }
}
