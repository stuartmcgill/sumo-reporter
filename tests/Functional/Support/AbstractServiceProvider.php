<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter\Tests\Functional\Support;

use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
use Laminas\ServiceManager\ServiceManager;

class AbstractServiceProvider
{
    /** @param array <string, mixed> $configOverrides */
    protected function initServiceManager(array $configOverrides = []): ServiceManager
    {
        $serviceManager = new ServiceManager();
        $serviceManager->addAbstractFactory(new ReflectionBasedAbstractFactory());

        $config = array_merge(include __DIR__ . '/../../../config/config.php', $configOverrides);
        $serviceManager->setService('config', $config);

        return $serviceManager;
    }
}
