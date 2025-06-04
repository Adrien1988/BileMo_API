<?php

namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\SecurityScheme;
use ApiPlatform\OpenApi\OpenApi;

final class OpenApiJwtDecorator implements OpenApiFactoryInterface
{


    public function __construct(
        private OpenApiFactoryInterface $decorated,
    ) {

    }


    /** @param array<string, mixed> $context */
    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);
        $components = $openApi->getComponents();

        $securitySchemes = ($components->getSecuritySchemes() ?? new \ArrayObject());

        $securitySchemes['JWT'] = new SecurityScheme(
            type: 'http',
            scheme: 'bearer',
            bearerFormat: 'JWT',
        );

        $components = $components->withSecuritySchemes($securitySchemes);
        $openApi = $openApi->withComponents($components)
                              ->withSecurity([['JWT' => []]]);

        return $openApi;

    }


}
