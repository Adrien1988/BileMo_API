<?php

namespace App\Tests\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\Components;
use ApiPlatform\OpenApi\Model\Info;
use ApiPlatform\OpenApi\Model\Paths;
use ApiPlatform\OpenApi\OpenApi;
use PHPUnit\Framework\TestCase;

class OpenApiJwtDecoratorTest extends TestCase
{


    public function testAddsJwtScheme(): void
    {
        // 1. Fabrique factice minimaliste
        $dummyFactory = new class () implements OpenApiFactoryInterface {


            public function __invoke(array $context = []): OpenApi
            {
                return new OpenApi(
                    new Info('Dummy API', '1.0.0'),
                    [],
                    new Paths(),
                    new Components(),
                );
            }


        };

        // 2. On décore
        $decorator = new \App\OpenApi\OpenApiJwtDecorator($dummyFactory);
        $openApi = $decorator();

        // 3. On vérifie que le schéma « JWT » est bien là
        $this->assertArrayHasKey('JWT', $openApi->getComponents()->getSecuritySchemes());
    }


}
