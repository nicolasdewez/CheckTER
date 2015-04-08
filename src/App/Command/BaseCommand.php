<?php

namespace App\Command;

use App\Exception\BadConfigureException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BaseCommand.
 */
abstract class BaseCommand extends ContainerCommand
{
    /** @var array */
    protected $configuration;

    /**
     * @param InputInterface $input
     *
     * @throws BadConfigureException
     */
    protected function checkLoadAndSave(InputInterface $input)
    {
        if ($input->getOption('save') && $input->getOption('load')) {
            throw new BadConfigureException('Save and load options mustn\'t be used in same time');
        }

        $configurationName = $input->getOption('save') ?: '';
        $configurationName = $configurationName ?:  $input->getOption('load');
        if (preg_match('#[/\# ]#', $configurationName)) {
            throw new BadConfigureException('Bad name of configuration');
        }
    }

    /**
     * @param string          $type
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function saveConfiguration($type, InputInterface $input, OutputInterface $output)
    {
        $output->writeln(PHP_EOL.'<info>Saving configuration...</info>');
        $pathFile = __PATH_CONFIGURATION__.'/'.$type.'/'.base64_encode($input->getOption('save'));
        file_put_contents($pathFile, json_encode($this->configuration));
    }

    /**
     * @param string          $type
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws BadConfigureException
     */
    protected function loadConfiguration($type, InputInterface $input, OutputInterface $output)
    {
        $output->writeln(PHP_EOL.'<info>Loading configuration...</info>');
        $pathFile = __PATH_CONFIGURATION__.'/'.$type.'/'.base64_encode($input->getOption('load'));
        if (!is_file($pathFile)) {
            throw new BadConfigureException(sprintf('Configuration %s doesn\'t exists', $input->getOption('load')));
        }
        $this->configuration = json_decode(file_get_contents($pathFile), true);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    abstract protected function interactive(InputInterface $input, OutputInterface $output);

    /**
     * @param OutputInterface $output
     */
    abstract protected function displayResult(OutputInterface $output);
}
