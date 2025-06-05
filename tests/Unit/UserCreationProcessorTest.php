<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use ApiPlatform\Metadata\Post;
use App\Entity\User;
use App\Enum\UserRole;
use App\Repository\UserRepository;
use App\State\UserCreationProcessor;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @covers \App\State\UserCreationProcessor
 */
final class UserCreationProcessorTest extends TestCase
{


    /**
     * @param Security&MockObject $security
     *
     * @phpstan-param Security&MockObject $security
     */
    private function getProcessor(Security $security): UserCreationProcessor
    {
        /** @var EntityManagerInterface&MockObject $em */
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('persist');
        $em->method('flush');

        /** @var UserRepository&MockObject $userRepo */
        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('findOneBy')->willReturn(null);

        return new UserCreationProcessor($em, $security, $userRepo);

    }


    public function testThrowsLogicExceptionWhenUserIsNotEntity(): void
    {
        /** @var UserInterface&MockObject $otherUser */
        $otherUser = $this->createStub(UserInterface::class);

        /** @var Security&MockObject $security */
        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($otherUser);

        $processor = $this->getProcessor($security);

        $this->expectException(\LogicException::class);
        $processor->process(new User(), new Post());

    }


    public function testThrowsAccessDeniedWhenUserHasNoAdminRole(): void
    {
        $plainUser = (new User())
            ->setEmail('nobody@example.com')
            ->setPassword('x')
            ->setFirstName('No')
            ->setLastName('Role')
            ->setRole(UserRole::ROLE_USER);

        /** @var Security&MockObject $security */
        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($plainUser);

        $processor = $this->getProcessor($security);

        $this->expectException(AccessDeniedHttpException::class);
        $processor->process(new User(), new Post());

    }


}
