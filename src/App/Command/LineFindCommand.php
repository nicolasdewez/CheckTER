<?php

namespace App\Command;

use App\Service\Configuration;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class LineFindCommand.
 */
class LineFindCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:line:find')
            ->setDescription('Get line for stations')
            ->addOption('start', null, InputOption::VALUE_REQUIRED, 'Start station')
            ->addOption('end', null, InputOption::VALUE_REQUIRED, 'End station')
            ->addOption('save', 's', InputOption::VALUE_REQUIRED, 'Save configuration in file')
            ->addOption('load', 'l', InputOption::VALUE_REQUIRED, 'Load configuration from file')
            ->addOption('display-stops', 'd', InputOption::VALUE_NONE, 'Display stops')
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
            $this->loadConfiguration(Configuration::LINE, $input, $output);
        }

        if ($input->getOption('save')) {
            $this->saveConfiguration(Configuration::LINE, $input, $output);
            $this->saveLines($input);
        }

        $this->configuration['stops'] = $input->getOption('display-stops');
        $this->displayResult($output);
    }

    /**
     * @param InputInterface $input
     */
    protected function saveLines(InputInterface $input)
    {
        $configSrv = $this->getContainer()->get('app.configuration');
        foreach ($this->configuration['lines'] as $line) {
            $data = [
                'start' => $this->configuration['start'],
                'end' => $this->configuration['end'],
                'lines' => [$line],
            ];

            $name = sprintf('%s_%s', $input->getOption('save'), $line['code']);
            $configSrv->save(Configuration::LINE, $name, $data);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function interactive(InputInterface $input, OutputInterface $output)
    {
        $this->getStation($input, $output, 'start');
        $this->getStation($input, $output, 'end');

        $this->configuration['lines'] = $this->getContainer()->get('app.client_sncf')->getLines(
            $this->configuration['start']['station']['code'],
            $this->configuration['end']['station']['code'],
            $input->getOption('display-stops')
        );
    }

    /**
     * If parameter for station, load configuration else question to load.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param string          $station
     */
    protected function getStation(InputInterface $input, OutputInterface $output, $station)
    {
        $questionHelper = $this->getHelper('question');
        $configurationSrv = $this->getContainer()->get('app.configuration');
        if ($input->getOption($station)) {
            $this->configuration[$station] = $configurationSrv->load('station', $input->getOption($station));
        } else {
            $configurations = $configurationSrv->get('station');
            $question = $this->getContainer()->get('app.interactive_command')->getQuestionLineLoadStation($configurations, $station);
            $start = $questionHelper->ask($input, $output, $question);
            $this->configuration[$station] = $configurationSrv->load('station', $start);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function displayResult(OutputInterface $output)
    {
        $start = $this->configuration['start']['station'];
        $end = $this->configuration['end']['station'];

        $output->writeln(PHP_EOL.PHP_EOL.'<info>Results</info>');
        $output->writeln('<info>Stations : </info>');
        $table = new Table($output);
        $table->setHeaders(['Point', 'Name', 'Code']);
        $table->addRow(['Start', $start['name'], $start['code']]);
        $table->addRow(['End', $end['name'], $end['code']]);
        $table->render();

        $output->writeln('<info>Lines : </info>');
        $headers = $this->configuration['stops'] ? ['Name', 'Code', 'Stop'] : ['Name', 'Code'];

        $table = new Table($output);
        $table->setHeaders($headers);
        foreach ($this->configuration['lines'] as $key => $line) {
            if (0 !== $key && $this->configuration['stops']) {
                $table->addRow(new TableSeparator());
            }
            $nbStops = count($line['stops']);
            $row = $this->configuration['stops'] ? [
                new TableCell($line['name'], ['rowspan' => $nbStops]),
                new TableCell($line['code'], ['rowspan' => $nbStops]),
                isset($line['stops'][0]) ? $line['stops'][0]['name'] : '',
            ] : [$line['name'], $line['code']] ;

            $table->addRow($row);

            if ($this->configuration['stops']) {
                for ($i=1; $i<$nbStops; $i++) {
                    $table->addRow([$line['stops'][$i]['name']]);
                }
            }
        }
        $table->render();
    }
}
