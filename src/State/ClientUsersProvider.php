<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Client;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/** @implements ProviderInterface<User> */
final class ClientUsersProvider implements ProviderInterface
{


    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $em,
        private Security $security,
    ) {

    }


    /** @return iterable<User> */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new \LogicException('No authenticated user.');
        }

        // Si SUPER_ADMIN → on utilise l'ID client fourni dans l'URL
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true)) {
            $clientId = $uriVariables['id'] ?? null;

            if ($clientId === null) {
                throw new \InvalidArgumentException('Client ID is required for ROLE_SUPER_ADMIN.');
            }

            $client = $this->em->getRepository(Client::class)->find($clientId);

            if ($client === null) {
                throw new \InvalidArgumentException('Client not found.');
            }

            return $this->userRepository->findBy(['client' => $client]);
        }

        // Si ADMIN → il peut uniquement accéder aux utilisateurs de son propre client
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $client = $user->getClient();

            if ($client === null) {
                throw new \LogicException('Admin user has no client associated.');
            }

            $requestedClientId = $uriVariables['id'] ?? null;

            // L'admin ne peut accéder qu'à son propre client
            if ($requestedClientId === null || (int) $requestedClientId !== $client->getId()) {
                throw new AccessDeniedException('You are not allowed to access this client’s users.');
            }

            return $this->userRepository->findBy(['client' => $client]);
        }

        throw new AccessDeniedException('You are not allowed to access this resource.');

    }


}
