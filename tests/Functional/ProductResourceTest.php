<?php

namespace App\Tests\Functional;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\DataFixtures\ProductFixtures;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;

class ProductResourceTest extends ApiTestCase
{
    use JwtAuthenticatedClientTrait;


    /** Charge 150 produits avant la toute première méthode de test */
    public static function setUpBeforeClass(): void
    {
        self::bootKernel();
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $loader = new Loader();
        $loader->addFixture(new ProductFixtures());

        $executor = new ORMExecutor($em, new ORMPurger($em));
        $executor->execute($loader->getFixtures());

    }


    public function testDefaultPaginationIs30(): void
    {
        $client = $this->createAuthenticatedClient();
        $response = $client->request('GET', '/api/products');

        $this->assertResponseIsSuccessful();
        $this->assertCount(30, $response->toArray()['hydra:member']);

    }


    public function testItemsPerPageAndPageParams(): void
    {
        $client = $this->createAuthenticatedClient();
        $response = $client->request('GET', '/api/products?page=2&itemsPerPage=5');

        $this->assertResponseIsSuccessful();
        $this->assertCount(5, $response->toArray()['hydra:member']);

    }


    public function testOrderByNameAsc(): void
    {
        $client = $this->createAuthenticatedClient();
        $data = $client->request('GET', '/api/products?order[name]=asc&itemsPerPage=100')
                         ->toArray()['hydra:member'];

        $names = array_column($data, 'name');
        $sorted = $names;
        sort($sorted, SORT_NATURAL | SORT_FLAG_CASE);

        $this->assertSame($sorted, $names);

    }


    public function testOrderByPriceDesc(): void
    {
        $client = $this->createAuthenticatedClient();
        $data = $client->request('GET', '/api/products?order[price]=desc&itemsPerPage=100')
                         ->toArray()['hydra:member'];

        $prices = array_map('floatval', array_column($data, 'price'));
        $sorted = $prices;
        rsort($sorted, SORT_NUMERIC);

        $this->assertSame($sorted, $prices);

    }


}
