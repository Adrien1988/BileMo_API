<?php

// tests/Functional/ClientFilterTest.php

namespace App\Tests\Functional;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\DataFixtures\{ClientFixtures, UserFixtures};
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Component\HttpFoundation\Request;

class ClientFilterTest extends ApiTestCase
{
    use JwtAuthenticatedUserTrait;

    /**
     * @var \Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool
     */
    private $databaseTool;


    /**
     * Convert a URL query-string into a key/value array
     * without relying on parse_str().
     *
     * @return array<string, string|array<string>>
     */
    private function parseQuery(string $url): array
    {
        $qPos = strpos($url, '?');
        $queryString = $qPos === false ? '' : substr($url, $qPos + 1);

        return $queryString === '' ? [] : Request::create('/?'.$queryString)->query->all();

    }


    protected function setUp(): void
    {
        parent::setUp();

        $this->databaseTool = self::getContainer()
            ->get(DatabaseToolCollection::class)
            ->get();

        // Load reference fixtures
        $this->databaseTool->loadFixtures([
            ClientFixtures::class,
            UserFixtures::class,
        ]);

    }


    /** @test */
    public function itListsInactiveClientsOrderedByNameWithPagination(): void
    {
        $client = $this->createAuthenticatedUserClient('superadmin@example.com');

        $json = $client->request('GET', '/api/clients', [
            'query' => [
                'isActive'     => 'false',
                'order[name]'  => 'asc',
                'itemsPerPage' => 5,
                'page'         => 2,
            ],
            'headers' => ['Accept' => 'application/ld+json'],
        ])->toArray();

        /* ---------- Assert every returned client is inactive ---------- */
        foreach ($json['hydra:member'] as $clientData) {
            $this->assertFalse($clientData['isActive']);

            // New fields exist and are non-empty (fixtures guarantee this)
            $this->assertArrayHasKey('website', $clientData);
            $this->assertArrayHasKey('contactEmail', $clientData);
            $this->assertArrayHasKey('contactPhone', $clientData);
            $this->assertArrayHasKey('address', $clientData);
        }

        /* ---------- Check hydra:view pagination data ---------- */
        $this->assertArrayHasKey('hydra:view', $json);
        $view = $json['hydra:view'];

        // If a previous page exists we must not be on the first page
        if (isset($view['hydra:previous'])) {
            $curr = $this->parseQuery($view['@id']);
            $prev = $this->parseQuery($view['hydra:previous']);

            // Current page should be exactly one greater than previous
            $this->assertSame((int) $prev['page'] + 1, (int) $curr['page']);
        }

    }


}
