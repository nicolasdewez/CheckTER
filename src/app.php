#!/usr/bin/env php
<?php

require __DIR__.'/../vendor/autoload.php';
set_time_limit(0);

define('__PATH_CONFIGURATION__', realpath(__DIR__.'/../configuration'));

use App\Application\Application;
use App\Command\StationFindCommand;
use App\Command\LineFindCommand;
use App\Command\TimeFindCommand;
use App\Command\ConfigurationListCommand;

$application = new Application(__DIR__.'/App/config/services.xml');
$application->addContainerCommand(new StationFindCommand());
$application->addContainerCommand(new LineFindCommand());
$application->addContainerCommand(new TimeFindCommand());
$application->addContainerCommand(new ConfigurationListCommand());
$application->run();
