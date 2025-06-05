<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\DataFixtures\ClientFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\User;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;

final class UserDeleteTest extends ApiTestCase
{
    use JwtAuthenticatedUserTrait;

    /**
     * @var \Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool
     */
    private $databaseTool;
    
    private ReferenceRepository $referenceRepo;

    /** @var array<string,int> */
    private array $ids = [];


    protected function setUp(): void
    {
        parent::setUp();

        $this->databaseTool = self::getContainer()
            ->get(DatabaseToolCollection::class)
            ->get();

        $this->referenceRepo = $this->databaseTool
            ->loadFixtures([
                ClientFixtures::class,
                UserFixtures::class,
            ])
            ->getReferenceRepository();

        $this->ids = [
            'acmeUser'   => $this->referenceRepo->getReference(UserFixtures::REF_API_USER, User::class)->getId(),
            'globexUser' => $this->referenceRepo->getReference(UserFixtures::REF_OTHER_CLIENT_USER, User::class)->getId(),
        ];

    }


    public function testAdminDeletesOwnUser(): void
    {
        $client = $this->createAuthenticatedUserClient('admin@acme.com', 'adminsecret');
        $userId = $this->ids['acmeUser'];

        $client->request('DELETE', "/api/users/{$userId}");
        $this->assertResponseStatusCodeSame(204);

        $client->request('DELETE', "/api/users/{$userId}");
        $this->assertResponseStatusCodeSame(404);

    }


    public function testAdminCannotDeleteUserOfAnotherClient(): void
    {
        $client = $this->createAuthenticatedUserClient('admin@acme.com', 'adminsecret');
        $client->request('DELETE', '/api/users/'.$this->ids['globexUser']);
        $this->assertResponseStatusCodeSame(404);

    }


    public function testDeletingUnknownUserReturns404(): void
    {
        $client = $this->createAuthenticatedUserClient('admin@acme.com', 'adminsecret');
        $client->request('DELETE', '/api/users/999999');
        $this->assertResponseStatusCodeSame(404);

    }


    public function testDeleteWithoutJwtReturns401(): void
    {
        static::createClient()->request('DELETE', '/api/users/'.$this->ids['acmeUser']);
        $this->assertResponseStatusCodeSame(401);

    }


    public function testSuperAdminCanDeleteAnyUser(): void
    {
        $client = $this->createAuthenticatedUserClient('superadmin@example.com', 'supersecret');
        $client->request('DELETE', '/api/users/'.$this->ids['globexUser']);
        $this->assertResponseStatusCodeSame(204);

    }


}
