<?php

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
     * Convertit la query-string d’une URL en tableau clé/valeur
     * sans employer parse_str().
     *
     * @return array<string, string|array<string>> les paramètres de la query-string
     */
    private function parseQuery(string $url): array
    {
        $queryString = parse_url($url, PHP_URL_QUERY) ?? '';

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
        ])->toArray();

        foreach ($json['hydra:member'] as $clientData) {
            $this->assertFalse($clientData['isActive']);
        }

        $this->assertArrayHasKey('hydra:view', $json);
        $view = $json['hydra:view'];

        // S’il existe une page précédente, c’est qu’on n’est pas sur la première page.
        if (isset($view['hydra:previous'])) {
            $curr = $this->parseQuery($view['@id']);
            $prev = $this->parseQuery($view['hydra:previous']);

            // la page courante doit être > page précédente
            $this->assertSame((int) $prev['page'] + 1, (int) $curr['page']);
        }

    }


}
