<?php

namespace App\Tests\Functional;

use ApiPlatform\Symfony\Bundle\Test\Client as ApiTestClient;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

trait JwtAuthenticatedUserTrait
{


    /**
     * Crée un client API authentifié par JWT pour l'utilisateur voulu.
     *
     * @param string $email    Email de l'utilisateur de test
     * @param string $password (ignoré, pour compatibilité éventuelle future, ou tu peux le gérer selon ta stratégie)
     */
    private function createAuthenticatedUserClient(string $email = 'api@example.com', string $password = 'secret'): ApiTestClient
    {
        $container = static::getContainer();

        // @var UserRepository $userRepo

        $userRepo = $container->get(UserRepository::class);
        $user = $userRepo->findOneBy(['email' => $email]);

        if (!$user) {
            throw new \LogicException(sprintf("L'utilisateur de test '%s' n'existe pas dans la base de données fixtures.", htmlspecialchars($email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')));
        }

        // @var JWTTokenManagerInterface $jwt

        $jwt = $container->get(JWTTokenManagerInterface::class);
        $token = $jwt->create($user);

        return static::createClient([], [
            'auth_bearer' => $token,
        ]);

    }


}
