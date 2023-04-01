<?php

declare(strict_types=1);

namespace StuartMcGill\SumoScraper;

use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
use Laminas\ServiceManager\ServiceManager;

$serviceManager = new ServiceManager([
    'abstract_factories' => [new ReflectionBasedAbstractFactory()]
]);
