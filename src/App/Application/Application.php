<?php

namespace App\Application;

use App\Command\ContainerCommand;
use App\DependencyInjection\ContainerInjection;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * Class Application.
 */
class Application extends ConsoleApplication implements ContainerAwareInterface
{
    use ContainerInjection;

    /**
     * @param string $pathConfigFile
     * @param string $name
     * @param string $version
     */
    public function __construct($pathConfigFile, $name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        $this->initContainer($pathConfigFile);
        parent::__construct($name, $version);
    }

    /**
     * @param string $pathConfigFile
     */
    protected function initContainer($pathConfigFile)
    {
        $file = new \SplFileInfo($pathConfigFile);

        $container = new ContainerBuilder();
        $configDirectory = new FileLocator($file->getPath());
        $loader = new XmlFileLoader($container, $configDirectory);
        $loader->load($file->getBasename());

        $this->setContainer($container);
    }

    /**
     * @param ContainerCommand $command
     *
     * @return ContainerCommand
     */
    public function addContainerCommand(ContainerCommand $command)
    {
        $command->setContainer($this->container);

        return $this->add($command);
    }
}
