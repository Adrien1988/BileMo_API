<?php

namespace App\Tests\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\Components;
use ApiPlatform\OpenApi\Model\Info;
use ApiPlatform\OpenApi\Model\Paths;
use ApiPlatform\OpenApi\OpenApi;
use App\OpenApi\OpenApiJwtDecorator;
use PHPUnit\Framework\TestCase;

final class OpenApiJwtDecoratorTest extends TestCase
{


    public function testAddsJwtScheme(): void
    {
        $dummyFactory = new class () implements OpenApiFactoryInterface {


            /** @param array<string,mixed> $context */
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

        $decorator = new OpenApiJwtDecorator($dummyFactory);
        $openApi = $decorator();

        self::assertArrayHasKey('JWT', $openApi->getComponents()->getSecuritySchemes());

    }


}
