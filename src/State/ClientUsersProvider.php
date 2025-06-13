<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\User;
use App\Repository\UserRepository;

/** @implements ProviderInterface<User> */
final class ClientUsersProvider implements ProviderInterface
{


    public function __construct(
        private UserRepository $userRepository,
    ) {

    }


    /** @return iterable<User> */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        // On récupère l'ID du client dans l'URL
        $clientId = $uriVariables['id'] ?? null;

        if (null === $clientId) {
            throw new \InvalidArgumentException('Client ID is required.');
        }

        // On renvoie les Users liés à ce client
        return $this->userRepository->findBy(['client' => $clientId]);

    }


}
