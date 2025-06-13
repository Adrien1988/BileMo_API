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

        // Même jeu de données que le reste de la suite + super-admin
        $this->databaseTool->loadFixtures([
            ClientFixtures::class,
            UserFixtures::class,
            ProductFixtures::class,
        ]);

    }


    /** @test */
    public function superAdminCanCrudProduct(): void
    {
        // Super-admin défini dans UserFixtures
        $client = $this->createAuthenticatedUserClient('superadmin@example.com');
        $headers = [
            'Content-Type' => 'application/ld+json',
            'Accept'       => 'application/ld+json',
        ];

        /* --- CREATE --- */
        $response = $client->request('POST', '/api/products', [
            'headers' => $headers,
            'json'    => [
                'name'             => 'Galaxy Z-Ultra',
                'description'      => 'Nouvelle phablette pliable',
                'price'            => '1799.90',
                'brand'            => 'BileMo',
                'imageUrl'         => 'https://img.example.com/z-ultra.jpg',
                'color'            => 'Graphite',
                'storageCapacity'  => 512,
                'ram'              => 16,
                'screenSize'       => '7.2',
                'cameraResolution' => '200 MP',
                'operatingSystem'  => 'Android 15',
                'batteryCapacity'  => '6000 mAh',
            ],
        ]);
        $this->assertResponseStatusCodeSame(201);
        $iri = $response->toArray()['@id'];

        /* --- UPDATE --- */
        // On récupère l'état actuel pour faire un PUT complet
        $product = $client->request('GET', $iri)->toArray();

        $client->request('PUT', $iri, [
            'headers' => [
                'Content-Type' => 'application/ld+json',
                'Accept'       => 'application/ld+json',
            ],
            'json' => [
                'name'              => $product['name'],
                'description'       => $product['description'],
                'price'             => '1699.90', // changement ici
                'brand'             => $product['brand'],
                'imageUrl'          => $product['imageUrl'],
                'color'             => $product['color'],
                'storageCapacity'   => $product['storageCapacity'],
                'ram'               => $product['ram'],
                'screenSize'        => $product['screenSize'],
                'cameraResolution'  => $product['cameraResolution'],
                'operatingSystem'   => $product['operatingSystem'],
                'batteryCapacity'   => $product['batteryCapacity'],
            ],
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
            'json'    => [
                'name'             => 'Foo',
                'description'      => 'Bar',
                'price'            => '10.00',
                'brand'            => 'FooBrand',
                'imageUrl'         => 'https://example.com/foo.png',
                'color'            => 'Blue',
                'storageCapacity'  => 64,
                'ram'              => 4,
                'screenSize'       => '6.0',
                'cameraResolution' => '12 MP',
                'operatingSystem'  => 'Android 14',
                'batteryCapacity'  => '4000 mAh',
            ],
        ]);
        $this->assertResponseStatusCodeSame(403);

    }


}
