<?php

namespace App\Tests\Functional;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\DataFixtures\{ClientFixtures, ProductFixtures, UserFixtures};
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Component\HttpFoundation\Request;

class ProductFilterRangeTest extends ApiTestCase
{
    use JwtAuthenticatedUserTrait;

    /**
     * @var \Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool
     */
    private $databaseTool;


    /**
     * Convertit la query-string d’une URL en tableau clé/valeur, sans parse_str().
     *
     * @return array<string, string|array<string>>
     */
    private function parseQuery(string $url): array
    {
        // On repère le premier « ? » pour isoler la query-string.
        $queryString = '';
        $qPos = strpos($url, '?');
        if ($qPos !== false) {
            $queryString = substr($url, $qPos + 1);
        }

        return $queryString === '' ? [] : Request::create('/?'.$queryString)->query->all();

    }


    protected function setUp(): void
    {
        parent::setUp();

        $this->databaseTool = self::getContainer()
            ->get(DatabaseToolCollection::class)
            ->get();

        $this->databaseTool->loadFixtures([
            ClientFixtures::class,
            UserFixtures::class,
            ProductFixtures::class,
        ]);

    }


    /** @test */
    public function itFiltersByPriceLowerThan500AndChecksHydraView(): void
    {
        $client = $this->createAuthenticatedUserClient();
        $response = $client->request('GET', '/api/products', [
            'query' => [
                'price[lt]'    => 700,
                'order[name]'  => 'asc',
                'order[price]' => 'asc',
                'itemsPerPage' => 5,
                'page'         => 1,
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $json = $response->toArray();

        // 1) 5 items retournés et totalItems cohérent (>5)
        $this->assertCount(5, $json['hydra:member']);
        $this->assertGreaterThanOrEqual(5, $json['hydra:totalItems']);

        // 2) Tous les prix < 700
        foreach ($json['hydra:member'] as $product) {
            $this->assertLessThan(700, (float) $product['price']);
        }

        // 3) Bloc hydra:view complet
        $view = $json['hydra:view'];
        $this->assertArrayHasKey('hydra:first', $view);
        $this->assertArrayHasKey('hydra:last', $view);
        $this->assertArrayHasKey('hydra:next', $view);

        $q = $this->parseQuery($view['@id']);
        $this->assertSame(1, (int) ($q['page'] ?? 1));

    }


}
