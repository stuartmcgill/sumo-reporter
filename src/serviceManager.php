<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter;

use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
use Laminas\ServiceManager\ServiceManager;
use StuartMcGill\SumoApiPhp\Service\RikishiService;

$serviceManager = new ServiceManager([
    'factories' => [
        'config' => fn () => require_once __DIR__ . '/../config/config.php',
        RikishiService::class => fn () => RikishiService::factory(),
    ],
    'abstract_factories' => [new ReflectionBasedAbstractFactory()]
]);
