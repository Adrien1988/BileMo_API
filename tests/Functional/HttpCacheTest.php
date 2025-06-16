<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\DataFixtures\ClientFixtures;
use App\DataFixtures\ProductFixtures;
use App\DataFixtures\UserFixtures;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Component\HttpFoundation\Response;

final class HttpCacheTest extends ApiTestCase
{
    use JwtAuthenticatedUserTrait;


    protected function setUp(): void
    {
        self::getContainer()->get(DatabaseToolCollection::class)
            ->get()
            ->loadFixtures([
                ClientFixtures::class,
                UserFixtures::class,
                ProductFixtures::class,
            ]);

    }


    public function testEtagIsStableUntilResourceChanges(): void
    {
        $client = $this->createAuthenticatedUserClient('superadmin@example.com');

        /* 1. on récupère l’id du premier produit */
        $list = $client->request('GET', '/api/products')->toArray();
        $id = $list['hydra:member'][0]['id'];

        /* 2. première lecture → ETag A */
        $first = $client->request('GET', "/api/products/{$id}");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $etagA = $first->getHeaders()['etag'][0] ?? null;

        /* 3. deuxième lecture sans modification → même ETag */
        $second = $client->request('GET', "/api/products/{$id}");
        $etagB = $second->getHeaders()['etag'][0] ?? null;
        $this->assertSame($etagA, $etagB, 'L’ETag doit rester identique tant que la ressource ne change pas');

        /* 4. PUT : on change le prix → ETag différent */
        $product = $first->toArray();

        $client->request('PUT', "/api/products/{$id}", [
            'headers' => [
                'Content-Type' => 'application/ld+json',
                'Accept'       => 'application/ld+json',
            ],
            'json'    => [
                'name'              => $product['name'],
                'description'       => $product['description'],
                'price'             => '1234.00',
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

        $updated = $client->request('GET', "/api/products/{$id}");
        $etagC = $updated->getHeaders()['etag'][0] ?? null;
        $this->assertNotSame($etagA, $etagC, 'Nouvel ETag attendu après modification');

    }


}
