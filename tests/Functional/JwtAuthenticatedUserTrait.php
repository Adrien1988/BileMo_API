<?php

namespace App\Tests\Functional;

use ApiPlatform\Symfony\Bundle\Test\Client as ApiTestClient;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

trait JwtAuthenticatedUserTrait
{


    /**
     * Génère un JWT pour l’utilisateur identifié par son e-mail.
     *
     * @param string      $email    Email de l’utilisateur présent dans les fixtures
     * @param string|null $password Paramètre facultatif (ignoré, conservé pour compatibilité)
     */
    private function getJwt(string $email, ?string $password = null): string
    {
        $container = static::getContainer();

        /** @var UserRepository $userRepo */
        $userRepo = $container->get(UserRepository::class);
        $user = $userRepo->findOneBy(['email' => $email]);

        if (!$user) {
            throw new \LogicException(sprintf('L’utilisateur de test « %s » n’existe pas dans les fixtures.', htmlspecialchars($email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')));
        }

        /** @var JWTTokenManagerInterface $jwtManager */
        $jwtManager = $container->get(JWTTokenManagerInterface::class);

        return $jwtManager->create($user);

    }


    /**
     * Retourne un client API déjà authentifié (header Bearer).
     *
     * @param string      $email    Email de l’utilisateur ciblé
     * @param string|null $password Paramètre facultatif, laissé pour compatibilité
     */
    private function createAuthenticatedUserClient(
        string $email = 'api@example.com',
        ?string $password = null,
    ): ApiTestClient {
        $token = $this->getJwt($email, $password);

        return static::createClient([], [
            'auth_bearer' => $token,
        ]);

    }


}
