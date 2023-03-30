#!/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use StuartMcGill\SumoScraper\CliCommand\DownloadStreaks;
use Symfony\Component\Console\Application;

$app = new Application();

$app->add(new DownloadStreaks());

$app->run();
