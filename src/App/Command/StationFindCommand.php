<?php

namespace App\Command;

use App\Service\InteractiveCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Class StationFindCommand.
 */
class StationFindCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('apisncf:station:find')
            ->setDescription('Get station')
            ->addOption('save', 's', InputOption::VALUE_REQUIRED, 'Save configuration in file')
            ->addOption('load', 'l', InputOption::VALUE_REQUIRED, 'Load configuration from file')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkLoadAndSave($input, $output);

        if (!$input->getOption('load')) {
            $this->interactive($input, $output);
        } else {
            $this->loadConfiguration('station', $input, $output);
        }

        if ($input->getOption('save')) {
            $this->saveConfiguration('station', $input, $output);
        }

        $this->displayResult($output);
    }

    /**
     * {@inheritdoc}
     */
    protected function interactive(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getHelper('question');
        $question = new Question('<question>Please enter the name of the city :</question> ', 'Lille');
        $this->configuration['cityUser'] = $questionHelper->ask($input, $output, $question);

        /* @var InteractiveCommand */
        $interactiveCommand = $this->getContainer()->get('app.interactive_command');

        // Search cities
        $cities = $this->getContainer()->get('app.client_insee')->getCodeInsee($this->configuration['cityUser']);
        $this->configuration['city'] = $cities[0];
        if (1 < count($cities)) {
            $table = $interactiveCommand->getTableCities($output, $cities);
            $table->render();

            $question = $interactiveCommand->getQuestionCities($cities);
            $this->configuration['city'] = $questionHelper->ask($input, $output, $question);
        }

        // Search Stations
        $stations = $this->getContainer()->get('app.client_sncf')->getStations(
            $this->configuration['city']['insee'],
            $this->configuration['city']['name']
        );

        $this->configuration['station'] = $stations[0];
        if (1 < count($stations)) {
            $table = $interactiveCommand->getTableStations($output, $stations);
            $table->render();

            $question = $interactiveCommand->getQuestionStations($stations);
            $this->configuration['station'] = $questionHelper->ask($input, $output, $question);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function displayResult(OutputInterface $output)
    {
        $output->writeln(PHP_EOL.PHP_EOL.'<info>Results</info>');
        $output->writeln('<info>Station : </info>');
        $table = new Table($output);
        $table->setHeaders(['Field', 'Value']);
        $table->addRow(['Name', $this->configuration['station']['name']]);
        $table->addRow(['Code', $this->configuration['station']['code']]);
        $table->render();

        $output->writeln('<info>City : </info>');
        $table = new Table($output);
        $table->setHeaders(['Field', 'Value']);
        $table->addRow(['Name', $this->configuration['city']['name']]);
        $table->addRow(['Region', $this->configuration['city']['region'].' ('.$this->configuration['city']['department'].')']);
        $table->addRow(['Insee', $this->configuration['city']['insee']]);
        $table->render();
    }
}
