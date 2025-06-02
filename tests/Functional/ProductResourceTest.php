<?php

namespace App\Tests\Functional;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\DataFixtures\ClientFixtures;
use App\DataFixtures\ProductFixtures;
use App\DataFixtures\UserFixtures;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;

class ProductResourceTest extends ApiTestCase
{
    use JwtAuthenticatedUserTrait;

    private $databaseTool;


    protected function setUp(): void
    {
        parent::setUp();

        // Charge tous les jeux de fixtures nécessaires (ordre important !)
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
        $this->databaseTool->loadFixtures([
            ClientFixtures::class,
            UserFixtures::class,
            ProductFixtures::class,
        ]);

    }


    public function testDefaultPaginationIs30(): void
    {
        $client = $this->createAuthenticatedUserClient();
        $response = $client->request('GET', '/api/products');
        $this->assertResponseIsSuccessful();
        $this->assertCount(30, $response->toArray()['hydra:member']);

    }


    public function testItemsPerPageAndPageParams(): void
    {
        $client = $this->createAuthenticatedUserClient();
        $response = $client->request('GET', '/api/products?page=2&itemsPerPage=5');
        $this->assertResponseIsSuccessful();
        $this->assertCount(5, $response->toArray()['hydra:member']);

    }


    public function testOrderByNameAsc(): void
    {
        $client = $this->createAuthenticatedUserClient();
        $data = $client->request('GET', '/api/products?order[name]=asc&itemsPerPage=100')
            ->toArray()['hydra:member'];
        $names = array_column($data, 'name');
        $sorted = $names;
        sort($sorted, (SORT_NATURAL | SORT_FLAG_CASE));
        $this->assertSame($sorted, $names);

    }


    public function testOrderByPriceDesc(): void
    {
        $client = $this->createAuthenticatedUserClient();
        $data = $client->request('GET', '/api/products?order[price]=desc&itemsPerPage=100')
            ->toArray()['hydra:member'];
        $prices = array_map('floatval', array_column($data, 'price'));
        $sorted = $prices;
        rsort($sorted, SORT_NUMERIC);
        $this->assertSame($sorted, $prices);

    }


}
