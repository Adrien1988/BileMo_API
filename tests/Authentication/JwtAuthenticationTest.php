<?php

namespace App\Tests\Authentication;

use App\Entity\User;
use App\DataFixtures\UserFixtures;
use Doctrine\ORM\Tools\SchemaTool;
use App\DataFixtures\ClientFixtures;
use App\DataFixtures\ProductFixtures;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

final class JwtAuthenticationTest extends ApiTestCase
{
    /**
     * (Ré)-initialise la base de test et charge les fixtures.
     */
    private function loadFixtures(): void
    {
        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        /* --- 1. (Re)création du schéma ------------------------------- */
        $schemaTool = new SchemaTool($em);
        $metadata   = $em->getMetadataFactory()->getAllMetadata();

        // On repart d’une base propre
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        /* --- 2. Chargement des fixtures ------------------------------ */
        $loader = new Loader();
        $loader->addFixture($container->get(ClientFixtures::class));
        $loader->addFixture($container->get(UserFixtures::class));
        $loader->addFixture($container->get(ProductFixtures::class));

        $executor = new ORMExecutor($em, new ORMPurger($em));
        $executor->execute($loader->getFixtures());
    }

    public function test401WithoutToken(): void
    {
        static::createClient(['debug' => false])
            ->request('GET', '/api/products');

        self::assertResponseStatusCodeSame(401);
    }

    public function test200WithValidToken(): void
    {
        /* ---------- 1. Fixtures ------------------------------------- */
        $this->loadFixtures();

        /* ---------- 2. Génère le JWT -------------------------------- */
        $container = self::getContainer();

        /** @var User $user */
        $user = $container
            ->get(EntityManagerInterface::class)
            ->getRepository(User::class)
            ->findOneBy(['email' => 'api@example.com']);

        self::assertNotNull($user, 'Utilisateur de test introuvable');

        $token = $container
            ->get(JWTTokenManagerInterface::class)
            ->create($user);

        /* ---------- 3. Appel protégé -------------------------------- */
        static::createClient(['debug' => false])->request(
            'GET',
            '/api/products',
            ['headers' => ['Authorization' => "Bearer $token"]],
        );

        self::assertResponseIsSuccessful(); // 200
    }
}
