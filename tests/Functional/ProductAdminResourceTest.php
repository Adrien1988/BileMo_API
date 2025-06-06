<?php

namespace App\Tests\Functional;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\DataFixtures\{ClientFixtures, ProductFixtures, UserFixtures};
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;

class ProductAdminResourceTest extends ApiTestCase
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

        // Même jeux de données que l’autre test + un super-admin dans UserFixtures
        $this->databaseTool->loadFixtures([
            ClientFixtures::class,
            UserFixtures::class,
            ProductFixtures::class,
        ]);

    }


    /** @test */
    public function superAdminCanCrudProduct(): void
    {
        // E-mail présent dans UserFixtures
        $client = $this->createAuthenticatedUserClient('superadmin@example.com');
        $headers = [
            'Content-Type' => 'application/ld+json',
            'Accept'       => 'application/ld+json',
        ];

        /* --- CREATE --- */
        $response = $client->request('POST', '/api/products', [
            'headers' => $headers,
            'json'    => [
                'name'        => 'Galaxy Z-Ultra',
                'description' => 'Nouvelle phablette pliable',
                'price'       => '1799.90',
                'brand'       => 'BileMo',
                'imageUrl'    => 'https://img.example.com/z-ultra.jpg',
            ],
        ]);
        $this->assertResponseStatusCodeSame(201);
        $iri = $response->toArray()['@id'];

        /* --- UPDATE --- */
        $client->request('PATCH', $iri, [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
                'Accept'       => 'application/ld+json',
            ],
            'json'    => ['price' => '1699.90'],
        ]);
        $this->assertResponseStatusCodeSame(200);

        /* --- DELETE --- */
        $client->request('DELETE', $iri);
        $this->assertResponseStatusCodeSame(204);

    }


    /** @test */
    public function otherRolesGet403OnWrite(): void
    {
        // Utilisateur ROLE_USER chargé dans UserFixtures
        $client = $this->createAuthenticatedUserClient('api@example.com');
        $headers = [
            'Content-Type' => 'application/ld+json',
            'Accept'       => 'application/ld+json',
        ];

        $client->request('POST', '/api/products', [
            'headers' => $headers,
            'json'    => ['name' => 'Foo', 'price' => '10'],
        ]);
        $this->assertResponseStatusCodeSame(403);

    }


}
