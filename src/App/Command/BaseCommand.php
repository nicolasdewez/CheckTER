<?php

namespace App\Command;

use App\Exception\BadConfigureException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BaseCommand.
 */
abstract class BaseCommand extends ContainerCommand
{
    /** @var array */
    protected $configuration;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addOption('save', 's', InputOption::VALUE_REQUIRED, 'Save configuration in file')
            ->addOption('load', 'l', InputOption::VALUE_REQUIRED, 'Load configuration from file')
        ;
    }

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
        if (preg_match('#[/\#]#', $configurationName)) {
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
        $this->getContainer()->get('app.configuration')->save($type, $input->getOption('save'), $this->configuration);
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
        $this->configuration = $this->getContainer()->get('app.configuration')->load($type, $input->getOption('load'));
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
