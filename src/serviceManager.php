<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter;

use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
use Laminas\ServiceManager\ServiceManager;

$serviceManager = new ServiceManager([
    'factories' => [
        'config' => fn () => require_once __DIR__ . '/../config/config.php',
    ],
    'abstract_factories' => [new ReflectionBasedAbstractFactory()]
]);
