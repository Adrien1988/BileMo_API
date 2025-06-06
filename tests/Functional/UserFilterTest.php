<?php

namespace App\Tests\Functional;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\DataFixtures\{ClientFixtures, UserFixtures};
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;

class UserFilterTest extends ApiTestCase
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
    public function itFiltersByFirstnameAndDateAndOrdersLastname(): void
    {
        $client = $this->createAuthenticatedUserClient('superadmin@example.com');

        $data = $client->request('GET', '/api/users', [
            'query' => [
                'firstName'         => 'sup',
                'createdAt[before]' => '2025-01-01',
                'order[lastName]'   => 'asc',
                'itemsPerPage'      => 50,
            ],
        ])->toArray()['hydra:member'];

        foreach ($data as $user) {
            $this->assertStringContainsStringIgnoringCase('sup', $user['firstName']);
            $this->assertLessThan(
                new \DateTimeImmutable('2025-01-01'),
                new \DateTimeImmutable($user['createdAt'])
            );
        }

        // Vérifie l'ordre ascendant par nom
        $lastNames = array_column($data, 'lastName');
        $sorted = $lastNames;
        sort($sorted, SORT_NATURAL | SORT_FLAG_CASE);
        $this->assertSame($sorted, $lastNames);

    }


}
