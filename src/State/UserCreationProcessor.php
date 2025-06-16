<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @implements ProcessorInterface<User, User>
 */
class UserCreationProcessor implements ProcessorInterface
{


    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Security $security,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {

    }


    /**
     * @param User $data
     */
    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): User {
        $currentUser = $this->security->getUser();

        if (!$currentUser instanceof User) {
            throw new \LogicException('Unexpected user class: '.\get_class($currentUser));
        }

        $isAdmin = \in_array('ROLE_ADMIN', $currentUser->getRoles(), true);
        $isSuperAdmin = \in_array('ROLE_SUPER_ADMIN', $currentUser->getRoles(), true);

        if (!$isAdmin && !$isSuperAdmin) {
            throw new AccessDeniedHttpException('Insufficient permissions.');
        }

        // ---------- Cas ADMIN : client imposé + contrôle de fraude ----------

        if ($isAdmin) {
            $adminClient = $currentUser->getClient();
            $requestedClient = $data->getClient();

            // L’admin a renseigné un client différent → 403
            if ($requestedClient && $requestedClient !== $adminClient) {
                throw new AccessDeniedHttpException('Admin cannot assign another client.');
            }

            // On force le client de l’admin quoi qu’il arrive
            $data->setClient($adminClient);
        }

        // ---------- Cas SUPER-ADMIN : le client est obligatoire ----------

        if ($isSuperAdmin && !$data->getClient()) {
            throw new BadRequestHttpException('Super admin must provide a client.');
        }

        // ---------- Unicité email + client ----------

        if ($this->userRepository->findOneBy([
            'email'  => $data->getEmail(),
            'client' => $data->getClient(),
        ])) {
            throw new BadRequestHttpException('Email already used for this client.');
        }

        $data->setPassword(
            $this->passwordHasher->hashPassword($data, $data->getPassword())
        );
        $this->em->persist($data);
        $this->em->flush();

        return $data;

    }


}
