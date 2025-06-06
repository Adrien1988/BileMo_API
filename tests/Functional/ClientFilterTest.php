<?php

namespace App\Tests\Functional;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\DataFixtures\{ClientFixtures, UserFixtures};
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;

class ClientFilterTest extends ApiTestCase
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
            parse_str(parse_url($view['@id'], PHP_URL_QUERY), $curr);
            parse_str(parse_url($view['hydra:previous'], PHP_URL_QUERY), $prev);

            // la page courante doit être > page précédente
            $this->assertSame((int) $prev['page'] + 1, (int) $curr['page']);
        }

    }


}
