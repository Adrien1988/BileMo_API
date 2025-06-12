<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\DataFixtures\ClientFixtures;
use App\DataFixtures\UserFixtures;
use App\Repository\ClientRepository;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;

/**
 * Vérifie les règles d’accès aux ressources Client / User
 * selon le rôle authentifié.
 */
final class ClientUserResourceTest extends ApiTestCase
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

        // Chargement des fixtures (ordre important)
        $this->databaseTool->loadFixtures([
            ClientFixtures::class,
            UserFixtures::class,
        ]);

    }


    /**
     * Recherche l’id d’un client par son nom via l’API.
     *
     * @return int|null id du client ou null si absent
     */
    private function getClientIdByName(
        string $name,
        Client $client,
    ): ?int {
        $payload = $client->request(
            'GET',
            '/api/clients?name='.urlencode($name)
        )->toArray(false);

        return $payload['hydra:member'][0]['id'] ?? null;

    }


    public function testRoleUserCannotSeeClientsOrUsers(): void
    {
        $client = $this->createAuthenticatedUserClient('api@example.com', 'secret');

        // Liste des clients → 403
        $client->request('GET', '/api/clients');
        $this->assertResponseStatusCodeSame(403);

        // Fiche d’un client → 403
        $client->request('GET', '/api/clients/1');
        $this->assertResponseStatusCodeSame(403);

        // Users d’un client → 403
        $client->request('GET', '/api/clients/1/users');
        $this->assertResponseStatusCodeSame(403);

        // Produits → 200
        $client->request('GET', '/api/products');
        $this->assertResponseIsSuccessful();

    }


    public function testRoleAdminCanSeeOwnClientUsersOnly(): void
    {
        $container = static::getContainer();
        $clientRepo = $container->get(ClientRepository::class);

        // Admin ACME
        $client = $this->createAuthenticatedUserClient('admin@acme.com', 'adminsecret');

        $myClient = $clientRepo->findOneBy(['name' => 'Acme Corp']);
        self::assertNotNull($myClient);

        $myClientId = $myClient->getId();

        // Users de son client → 200
        $client->request('GET', "/api/clients/{$myClientId}/users");
        $this->assertResponseIsSuccessful();

        // Autre client (Globex)
        $otherClient = $clientRepo->createQueryBuilder('c')
            ->where('c.name != :mine')
            ->setParameter('mine', 'Acme Corp')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();

        self::assertNotNull($otherClient);

        $otherId = $otherClient->getId();

        // Users d’un autre client → 403
        $client->request('GET', "/api/clients/{$otherId}/users");
        $this->assertResponseStatusCodeSame(403);

        // Fiche d’un autre client → 403
        $client->request('GET', "/api/clients/{$otherId}");
        $this->assertResponseStatusCodeSame(403);

        // Liste des produits → 200
        $client->request('GET', '/api/products');
        $this->assertResponseIsSuccessful();

    }


    public function testSuperAdminCanSeeAllClientsAndUsers(): void
    {
        $client = $this->createAuthenticatedUserClient('superadmin@example.com', 'supersecret');

        // Liste clients → 200 (≥ 2)
        $data = $client->request('GET', '/api/clients')->toArray();
        $this->assertResponseIsSuccessful();
        $this->assertGreaterThanOrEqual(2, \count($data['hydra:member']));

        // Fiches clients (Acme + Globex) → 200
        $acmeId = $this->getClientIdByName('Acme Corp', $client);
        $globexId = $this->getClientIdByName('Globex', $client);

        $this->assertNotNull($acmeId);
        $this->assertNotNull($globexId);

        $client->request('GET', "/api/clients/{$acmeId}");
        $this->assertResponseIsSuccessful();

        $client->request('GET', "/api/clients/{$globexId}");
        $this->assertResponseIsSuccessful();

        // Users de chaque client → 200
        $client->request('GET', "/api/clients/{$acmeId}/users");
        $this->assertResponseIsSuccessful();

        $client->request('GET', "/api/clients/{$globexId}/users");
        $this->assertResponseIsSuccessful();

        // Liste users globale → 200
        $client->request('GET', '/api/users');
        $this->assertResponseIsSuccessful();

    }


    public function testAdminCannotSeeUsersOfOtherClient(): void
    {
        // Admin Globex
        $client = $this->createAuthenticatedUserClient('admin@other.com', 'adminsecret');

        /** @var ClientRepository $repo */
        $repo = static::getContainer()->get(ClientRepository::class);

        $my = $repo->findOneBy(['name' => 'Globex']);
        $other = $repo->findOneBy(['name' => 'Acme Corp']);

        self::assertNotNull($my);
        self::assertNotNull($other);
        self::assertNotSame($my->getId(), $other->getId());

        // Users de son client → 200
        $client->request('GET', "/api/clients/{$my->getId()}/users");
        $this->assertResponseIsSuccessful();

        // Users d’un autre client → 403
        $client->request('GET', "/api/clients/{$other->getId()}/users");
        $this->assertResponseStatusCodeSame(403);

    }


}
