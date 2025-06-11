<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client as ApiClient;
use App\DataFixtures\ClientFixtures;
use App\DataFixtures\ProductFixtures;
use App\DataFixtures\UserFixtures;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;

/**
 * Vérifie la sérialisation JSON-LD/Hydra niveau 3
 * et l’absence de référence circulaire.
 */
final class HypermediaTest extends ApiTestCase
{
    use JwtAuthenticatedUserTrait;

    /** @var \Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool */
    private $databaseTool;


    protected function setUp(): void
    {
        parent::setUp();

        $this->databaseTool = self::getContainer()
            ->get(DatabaseToolCollection::class)
            ->get();

        // on charge les mêmes fixtures que le reste de la suite
        $this->databaseTool->loadFixtures([
            ClientFixtures::class,
            ProductFixtures::class,
            UserFixtures::class,
        ]);

    }


    /** Extrait l’IRI d’un member (chaîne ou objet) */
    private function iri(mixed $member): ?string
    {
        return \is_array($member) ? ($member['@id'] ?? null) : $member;

    }


    /** Retourne un client API authentifié */
    private function api(string $email): ApiClient
    {
        return $this->createAuthenticatedUserClient($email);

    }


    /** @dataProvider collections */
    public function testCollectionHydraHeaders(string $uri, string $email): void
    {
        $res = $this->api($email)->request('GET', $uri, [
            'headers' => ['Accept' => 'application/ld+json'],
        ]);

        $this->assertResponseIsSuccessful();

        $data = $res->toArray(false);

        $this->assertArrayHasKey('@context', $data);
        $this->assertArrayHasKey('@id', $data);
        $this->assertArrayHasKey('@type', $data);
        $this->assertArrayHasKey('hydra:member', $data);
        $this->assertNotEmpty(
            $data['hydra:member'],
            "La collection {$uri} ne contient aucun élément."
        );

        // hydra:view facultatif
        if (isset($data['hydra:view'])) {
            $this->assertArrayHasKey('hydra:last', $data['hydra:view']);
        }

    }


    /** @dataProvider collections */
    public function testFirstItemNoCircular(string $uri, string $email): void
    {
        $client = $this->api($email);

        $col = $client->request('GET', $uri, ['headers' => ['Accept' => 'application/ld+json']])->toArray(false);
        $itemIri = $this->iri($col['hydra:member'][0]);
        $this->assertNotNull($itemIri, "Impossible de récupérer un IRI dans {$uri}");

        $item = $client->request('GET', $itemIri, ['headers' => ['Accept' => 'application/ld+json']]);
        $this->assertResponseIsSuccessful();
        $this->assertStringNotContainsString('CircularReference', $item->getContent());

    }


    public function testClientUsersAreIri(): void
    {
        $client = $this->api('superadmin@example.com');

        $col = $client->request('GET', '/api/clients', ['headers' => ['Accept' => 'application/ld+json']])->toArray(false);
        $clientIri = $this->iri($col['hydra:member'][0]);
        $this->assertNotNull($clientIri);

        $details = $client->request('GET', $clientIri, ['headers' => ['Accept' => 'application/ld+json']])->toArray(false);
        $this->assertIsArray($details['users'] ?? null);

        foreach ($details['users'] as $iri) {
            $this->assertMatchesRegularExpression('#^/api/users/\d+$#', $iri);
        }

    }


    /**
     * @return iterable<array{0:string,1:string}>
     */
    public static function collections(): iterable
    {
        return [
            ['/api/products', 'api@example.com'],
            ['/api/clients',  'superadmin@example.com'],
            ['/api/users',    'superadmin@example.com'],
        ];

    }


    /**
     * Vérifie que la collection /api/products expose bien hydra:view.hydra:next
     * quand elle contient plus d’items que la taille de page par défaut (30).
     */
    public function testProductsCollectionHasNextView(): void
    {
        // utilisateur simple qui peut lister les produits
        $client = $this->api('api@example.com');

        $response = $client->request('GET', '/api/products', [
            'headers' => ['Accept' => 'application/ld+json'],
        ]);

        $this->assertResponseIsSuccessful();

        $data = $response->toArray(false);

        // Les fixtures créent 150 produits, on vérifie qu'on dépasse 30
        $this->assertGreaterThan(
            30,
            $data['hydra:totalItems'] ?? 0,
            'Les fixtures doivent contenir > 30 produits pour tester la pagination.'
        );

        // hydra:view et hydra:next doivent exister
        $this->assertArrayHasKey('hydra:view', $data, 'hydra:view absent');
        $this->assertArrayHasKey('hydra:next', $data['hydra:view'], 'hydra:next absent dans hydra:view');

    }


}
