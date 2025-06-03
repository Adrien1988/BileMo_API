<?php

namespace App\Tests\Authentication;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\DataFixtures\ClientFixtures;
use App\DataFixtures\ProductFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\User;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

final class JwtAuthenticationTest extends ApiTestCase
{
    /**
     * (Ré)-initialise la base de test et charge les fixtures.
     */
    private function loadFixtures(): void
    {
        $container = self::getContainer();

        $em = $container->get(EntityManagerInterface::class);

        $schemaTool = new SchemaTool($em);
        $metadata = $em->getMetadataFactory()->getAllMetadata();

        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

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
        $this->loadFixtures();

        $container = self::getContainer();

        $user = $container
            ->get(EntityManagerInterface::class)
            ->getRepository(User::class)
            ->findOneBy(['email' => 'api@example.com']);

        self::assertNotNull($user, 'Utilisateur de test introuvable');

        $token = $container
            ->get(JWTTokenManagerInterface::class)
            ->create($user);

        static::createClient(['debug' => false])->request(
            'GET',
            '/api/products',
            ['headers' => ['Authorization' => "Bearer $token"]],
        );

        self::assertResponseIsSuccessful();
    }
}
