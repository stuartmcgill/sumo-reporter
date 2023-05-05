<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\Tests\Functional\Support;

use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
use Laminas\ServiceManager\ServiceManager;

class AbstractServiceProvider
{
    protected function initServiceManager(): ServiceManager
    {
        $serviceManager = new ServiceManager();
        $serviceManager->addAbstractFactory(new ReflectionBasedAbstractFactory());

        $config = include __DIR__ . '/../../../config/config.php';
        $serviceManager->setService('config', $config);

        return $serviceManager;
    }
}
