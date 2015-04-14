<?php

namespace App\Command;

use App\Service\Configuration;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class TimeFindCommand.
 */
class TimeFindCommand extends BaseCommand
{
    /** @var array */
    protected $results;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:time:find')
            ->setDescription('Get next timetable')
        ;

        parent::configure();
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
            $this->loadConfiguration(Configuration::TIME, $input, $output);
        }

        // Search
        $this->results = $this->getContainer()->get('app.client_sncf')->getTimes(
            $this->configuration['start']['station']['code'],
            $this->configuration['end']['station']['code'],
            new \DateTime($this->configuration['dateStart']),
            new \DateTime($this->configuration['dateEnd'])
        );

        if ($input->getOption('save')) {
            $this->saveConfiguration(Configuration::TIME, $input, $output);
        }

        $this->displayResult($output);
    }

    /**
     * {@inheritdoc}
     */
    protected function interactive(InputInterface $input, OutputInterface $output)
    {
        $this->getStation($input, $output, 'start');
        $this->getStation($input, $output, 'end');
        $this->getDateTime($input, $output, 'dateStart');
        $this->getDateTime($input, $output, 'dateEnd');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param string          $station
     */
    protected function getStation(InputInterface $input, OutputInterface $output, $station)
    {
        $questionHelper = $this->getHelper('question');
        $configurationSrv = $this->getContainer()->get('app.configuration');
        $configurations = $configurationSrv->get('station');
        $question = $this->getContainer()->get('app.interactive_command')->getQuestionLoadStation($configurations, $station);
        $response = $questionHelper->ask($input, $output, $question);
        $this->configuration[$station] = $configurationSrv->load('station', $response);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param string          $dateTime
     */
    protected function getDateTime(InputInterface $input, OutputInterface $output, $dateTime)
    {
        $questionHelper = $this->getHelper('question');

        $question = $this->getContainer()->get('app.interactive_command')->getQuestionSearchDate();
        $date = $questionHelper->ask($input, $output, $question);
        $question = $this->getContainer()->get('app.interactive_command')->getQuestionSearchTime($date);
        $this->configuration[$dateTime] = $questionHelper->ask($input, $output, $question)->format('Y-m-d H:i:s');
    }

    /**
     * {@inheritdoc}
     */
    protected function displayResult(OutputInterface $output)
    {
        dump($this->configuration, $this->results);
    }
}
