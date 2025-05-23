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
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

final class JwtAuthenticationTest extends ApiTestCase
{


    private function loadFixtures(): void
    {
        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        /* Purge la base --------------------------------------------------- */
        $purger = new ORMPurger($em);
        $purger->purge();                       // optionnel : exécute immédiatement

        /* Charge les fixtures -------------------------------------------- */
        $loader = new Loader();
        $loader->addFixture($container->get(ClientFixtures::class));
        $loader->addFixture($container->get(UserFixtures::class));
        $loader->addFixture($container->get(ProductFixtures::class));

        // 👉 on passe $purger au constructeur
        (new ORMExecutor($em, $purger))->execute($loader->getFixtures());
    }


    public function test401WithoutToken(): void
    {
        static::createClient()->request('GET', '/api/products');
        self::assertResponseStatusCodeSame(401);
    }


    public function test200WithValidToken(): void
    {
        /* ---------- (1) Fixtures ------------------------------------- */
        $this->loadFixtures();

        /* ---------- (2) Génère le JWT -------------------------------- */
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

        /* ---------- (3) Appel protégé -------------------------------- */
        static::createClient()->request('GET', '/api/products', [
            'headers' => ['Authorization' => "Bearer $token"],
        ]);

        self::assertResponseIsSuccessful(); // 200
    }


}
