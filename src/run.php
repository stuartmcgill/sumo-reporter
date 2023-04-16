#!/bin/env php
<?php

declare(strict_types=1);

namespace StuartMcGill\SumoReporter;

require __DIR__ . '/../vendor/autoload.php';
require_once 'serviceManager.php';

use Laminas\ServiceManager\ServiceManager;
use StuartMcGill\SumoReporter\CliCommand\DownloadStreaks;
use Symfony\Component\Console\Application;

$app = new Application();

/** @var ServiceManager $serviceManager */
$app->add($serviceManager->get(DownloadStreaks::class));

$app->run();
