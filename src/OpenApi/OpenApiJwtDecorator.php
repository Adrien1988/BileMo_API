<?php

namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\SecurityScheme;
use ApiPlatform\OpenApi\OpenApi;

/**
 * Ajoute un schéma « JWT » (bearer) à la doc OpenAPI
 * et le rend obligatoire pour toutes les routes.
 */
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

        /** @var \ArrayObject<string,SecurityScheme> $securitySchemes */
        $securitySchemes = $components->getSecuritySchemes() ?? new \ArrayObject();

        // Ajout / mise-à-jour du schéma JWT
        $securitySchemes['JWT'] = new SecurityScheme(
            type: 'http',
            scheme: 'bearer',
            bearerFormat: 'JWT',
        );

        // Ré-injection
        $components = $components->withSecuritySchemes($securitySchemes);
        $openApi = $openApi->withComponents($components)
                              ->withSecurity([['JWT' => []]]);

        return $openApi;

    }


}
