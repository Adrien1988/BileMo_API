<?php

namespace App\Serializer;

use ApiPlatform\Metadata\IriConverterInterface;

/**
 * Handler circulaire compatible « string callable » pour le Serializer.
 *
 * NB : Le Serializer appellera directement la méthode statique `handle()`
 * (voir configuration YAML ci-dessous).
 */
final class IriCircularHandler
{
    /** @var IriConverterInterface injecté une seule fois */
    private static ?IriConverterInterface $iriConverter = null;


    public function __construct(IriConverterInterface $iriConverter)
    {
        // mémorise l’instance pour la méthode statique
        self::$iriConverter = $iriConverter;

    }


    /**
     * Méthode statique appelée par le Serializer lorsque
     * la référence circulaire est détectée.
     */
    public static function handle(object $object): string
    {
        // sécurité : si, pour une raison quelconque, le service n’a
        // pas encore été injecté.
        if (null === self::$iriConverter) {
            throw new \LogicException('IriCircularHandler not initialized.');
        }

        return self::$iriConverter->getIriFromResource($object);

    }


}
