<?php

use App\Kernel;
use App\CacheKernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    $kernel = new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);

    // ➜ Active le proxy interne sauf en mode CLI ou tests
    if (PHP_SAPI !== 'cli' && !$kernel->isDebug()) {
        $kernel = new CacheKernel($kernel);
    }

    return $kernel;
};
