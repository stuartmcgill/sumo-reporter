<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper;

require_once __DIR__ . '/../config/config.php';

use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
use Laminas\ServiceManager\ServiceManager;

$serviceManager = new ServiceManager([
    'factories' => [
        /** @phpstan-ignore-next-line */
        'config' => fn () => $config,
    ],
    'abstract_factories' => [new ReflectionBasedAbstractFactory()]
]);
