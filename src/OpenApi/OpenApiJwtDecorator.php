<?php

namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\SecurityScheme;
use ApiPlatform\OpenApi\OpenApi;
use ArrayObject;

final class OpenApiJwtDecorator implements OpenApiFactoryInterface
{
    public function __construct(
        private OpenApiFactoryInterface $decorated,
    ) {
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);

        /* 1. On récupère les components existants */
        $components      = $openApi->getComponents();
        $securitySchemes = $components->getSecuritySchemes();

        // getSecuritySchemes() peut retourner null → on force en ArrayObject vide
        if (!$securitySchemes instanceof ArrayObject) {
            $securitySchemes = new ArrayObject($securitySchemes ?? []);
        }

        /* 2. On ajoute notre schéma JWT */
        $securitySchemes['JWT'] = new SecurityScheme(
            type: 'http',
            scheme: 'bearer',
            bearerFormat: 'JWT',
        );

        /* 3. On ré-injecte les components mis à jour */
        $components = $components->withSecuritySchemes($securitySchemes);
        $openApi    = $openApi->withComponents($components);

        /* 4. (facultatif) On rend JWT obligatoire partout */
        $openApi = $openApi->withSecurity([['JWT' => []]]);

        return $openApi;
    }
}
