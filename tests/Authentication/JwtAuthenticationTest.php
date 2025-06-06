<?php

declare(strict_types=1);

namespace App\Tests\Authentication;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\DataFixtures\ClientFixtures;
use App\DataFixtures\ProductFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

/**
 * Vérifie l’authentification JWT :
 *   – 401 sans jeton
 *   – 200 avec un jeton valide
 */
final class JwtAuthenticationTest extends ApiTestCase
{

    private AbstractDatabaseTool $db;


    protected function setUp(): void
    {
        parent::setUp();

        /** @var AbstractDatabaseTool $db */
        $db = self::getContainer()->get(DatabaseToolCollection::class)->get();
        $this->db = $db;

        // chargement systématique des fixtures avant chaque test
        $this->db->loadFixtures([
            ClientFixtures::class,
            UserFixtures::class,
            ProductFixtures::class,
        ]);

    }


    /** Sans JWT → 401 */
    public function test401WithoutToken(): void
    {
        static::createClient()
            ->request('GET', '/api/products');

        self::assertResponseStatusCodeSame(401);

    }


    /** Avec un JWT valide → 200 */
    public function test200WithValidToken(): void
    {

        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        /** @var User|null $user */
        $user = $em->getRepository(User::class)
                   ->findOneBy(['email' => 'api@example.com']);

        self::assertNotNull($user, 'Utilisateur de test introuvable.');

        $token = $container
            ->get(JWTTokenManagerInterface::class)
            ->create($user);

        static::createClient()->request(
            'GET',
            '/api/products',
            [
                'headers' => [
                    'Authorization' => sprintf('Bearer %s', $token),
                ],
            ],
        );

        self::assertResponseIsSuccessful();

    }


}
