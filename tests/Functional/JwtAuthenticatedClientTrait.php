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


    private function createAuthenticatedClient(): ApiTestClient
    {
        $container = static::getContainer();

        $userRepo = $container->get(UserRepository::class);
        $user = $userRepo->findOneBy(['email' => 'api@example.com']);

        if (!$user) {
            $em = $container->get(EntityManagerInterface::class);

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

        $jwt = $container->get(JWTTokenManagerInterface::class);
        $token = $jwt->create($user);

        return static::createClient([], ['auth_bearer' => $token]);

    }


}
