<?php

declare(strict_types=1);

namespace App\Tests\Unit\Serializer;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Serializer\IriCircularHandler;
use LogicException;
use PHPUnit\Framework\TestCase;

final class IriCircularHandlerTest extends TestCase
{


    protected function tearDown(): void
    {
        // On remet la propriété statique à null entre chaque test
        $ref = new \ReflectionClass(IriCircularHandler::class);
        $prop = $ref->getProperty('iriConverter');
        $prop->setAccessible(true);
        $prop->setValue(null, null);

    }


    /** Vérifie que handle() renvoie l’IRI quand le service est injecté */
    public function testHandleReturnsIriWithInjectedConverter(): void
    {
        $object = new \stdClass();

        /** @var IriConverterInterface&\PHPUnit\Framework\MockObject\MockObject $converter */
        $converter = $this->createMock(IriConverterInterface::class);
        $converter->expects($this->once())
            ->method('getIriFromResource')
            ->with($object)
            ->willReturn('/api/dummy/1');

        // Injection via le constructeur :
        new IriCircularHandler($converter);

        $iri = IriCircularHandler::handle($object);

        $this->assertSame('/api/dummy/1', $iri);

    }


    /** Vérifie qu’une LogicException est levée si handle() est appelé avant injection */
    public function testHandleThrowsWhenNotInitialized(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('IriCircularHandler not initialized.');

        IriCircularHandler::handle(new \stdClass());

    }


}
