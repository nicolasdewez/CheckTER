#!/usr/bin/env php
<?php

require __DIR__.'/../vendor/autoload.php';
set_time_limit(0);

use App\Command\StationFindCommand;
use App\Command\LineFindCommand;
use App\Command\TimeFindCommand;
use App\Command\ConfigurationListCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

$container = new ContainerBuilder();
$configDirectory = new FileLocator(__DIR__.'/App/config/');
$loader = new XmlFileLoader($container, $configDirectory);
$loader->load('dic.xml');

$application = new Application();
$application->add(new StationFindCommand(
    $container->get('app.client_sncf'),
    $container->get('app.client_insee'),
    $container->get('app.interactive_command')
));
$application->add(new LineFindCommand($container->get('app.client_sncf')));
$application->add(new TimeFindCommand($container->get('app.client_sncf')));
$application->add(new ConfigurationListCommand());
$application->run();
