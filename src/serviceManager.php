<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper;

use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
use Laminas\ServiceManager\ServiceManager;

$serviceManager = new ServiceManager([
    'factories' => [
        'config' => fn () => [
            'divisions' => ['Makuuchi', 'Juryo', 'Makushita', 'Sandanme', 'Jonidan', 'Jonokuchi'],
            'apiRateLimit' => 500,
        ],
    ],
    'abstract_factories' => [new ReflectionBasedAbstractFactory()]
]);
