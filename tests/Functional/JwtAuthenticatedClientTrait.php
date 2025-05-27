<?php

// tests/Functional/JwtAuthenticatedClientTrait.php

namespace App\Tests\Functional;

use ApiPlatform\Symfony\Bundle\Test\Client as ApiTestClient;
use App\Entity\Client as BusinessClient;
use App\Entity\User;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

trait JwtAuthenticatedClientTrait
{


    /** Retourne un client HTTP déjà authentifié avec un JWT */
    private function createAuthenticatedClient(): ApiTestClient
    {
        $container = static::getContainer();

        /** @var UserRepository $userRepo */
        $userRepo = $container->get(UserRepository::class);
        $user = $userRepo->findOneBy(['email' => 'api@example.com']);

        // Si l’utilisateur n’existe pas encore (DB vide), on le crée avec un client rattaché
        if (!$user) {
            /** @var EntityManagerInterface $em */
            $em = $container->get(EntityManagerInterface::class);

            /** @var ClientRepository $clientRepo */
            $clientRepo = $container->get(ClientRepository::class);
            $business = $clientRepo->findOneBy([]);

            if (!$business) {
                $business = (new BusinessClient())->setName('Test-Client');
                $em->persist($business);
            }

            $user = (new User())
                ->setEmail('api@example.com')
                ->setPassword('dummy')
                ->setFirstName('API')
                ->setLastName('User')
                ->setClient($business);

            $em->persist($user);
            $em->flush();
        }

        /** @var JWTTokenManagerInterface $jwt */
        $jwt = $container->get(JWTTokenManagerInterface::class);
        $token = $jwt->create($user);

        // Crée et retourne le client HTTP avec le header Authorization: Bearer …
        return static::createClient([], ['auth_bearer' => $token]);
    }


}
