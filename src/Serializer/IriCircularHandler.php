<?php

namespace App\Serializer;

use ApiPlatform\Metadata\IriConverterInterface;

final class IriCircularHandler
{


    public function __construct(
        private readonly IriConverterInterface $iriConverter,
    ) {

    }


    public function __invoke(object $object): string
    {
        // Ex.: /api/products/1
        return $this->iriConverter->getIriFromResource($object);

    }


}
