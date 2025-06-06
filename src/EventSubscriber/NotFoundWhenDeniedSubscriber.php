<?php

// src/EventSubscriber/NotFoundWhenDeniedSubscriber.php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Convertit l'AccessDenied (403) en NotFound (404) lors d'un DELETE /api/users/{id}.
 */
final class NotFoundWhenDeniedSubscriber implements EventSubscriberInterface
{


    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];

    }


    public function onKernelException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();
        $request = $event->getRequest();

        // Nous ne transformons l'erreur que si :
        //  1) c'est bien une AccessDeniedHttpException,
        //  2) la méthode est DELETE,
        //  3) le chemin pointe sous /api/users/
        if ($throwable instanceof AccessDeniedHttpException
            && $request->isMethod('DELETE')
            && str_starts_with($request->getPathInfo(), '/api/users/')
        ) {
            $event->setThrowable(new NotFoundHttpException());
        }

    }


}
