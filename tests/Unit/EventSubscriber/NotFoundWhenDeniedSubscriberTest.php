<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventSubscriber;

use App\EventSubscriber\NotFoundWhenDeniedSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class NotFoundWhenDeniedSubscriberTest extends TestCase
{


    /** Vérifie la déclaration de l’événement écouté */
    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(
            [KernelEvents::EXCEPTION => 'onKernelException'],
            NotFoundWhenDeniedSubscriber::getSubscribedEvents()
        );

    }


    /** Doit convertir AccessDenied → NotFound pour DELETE /api/users/{id} */
    public function testConvertsAccessDeniedToNotFound(): void
    {
        /** @var HttpKernelInterface&\PHPUnit\Framework\MockObject\MockObject $kernel */
        $kernel = $this->createStub(HttpKernelInterface::class);
        $request = Request::create('/api/users/42', Request::METHOD_DELETE);
        $exception = new AccessDeniedHttpException();

        $event = new ExceptionEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        (new NotFoundWhenDeniedSubscriber())->onKernelException($event);

        $this->assertInstanceOf(NotFoundHttpException::class, $event->getThrowable());

    }


    /** Ne doit rien changer pour les autres requêtes */
    public function testLeavesOtherRequestsUntouched(): void
    {
        /** @var HttpKernelInterface&\PHPUnit\Framework\MockObject\MockObject $kernel */
        $kernel = $this->createStub(HttpKernelInterface::class);
        $request = Request::create('/api/products/1', Request::METHOD_DELETE);
        $exception = new AccessDeniedHttpException();

        $event = new ExceptionEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        (new NotFoundWhenDeniedSubscriber())->onKernelException($event);

        $this->assertSame($exception, $event->getThrowable());

    }


}
