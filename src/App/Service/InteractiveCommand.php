<?php

namespace App\Service;

use App\Exception\BadConfigureException;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Question\Question;

/**
 * Class InteractiveCommand.
 */
class InteractiveCommand
{
    /** @var Configuration */
    protected $configSrv;

    /**
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configSrv = $configuration;
    }

    /**
     * @param OutputInterface $output
     * @param array           $cities
     *
     * @return Table
     */
    public function getTableCities(OutputInterface $output, array $cities)
    {
        $table = new Table($output);
        $table->setHeaders(['', 'City', 'Region']);
        foreach ($cities as $key => $city) {
            $key = sprintf('<info>%d</info>', $key);
            $table->addRow([$key, $city['name'], $city['region'].' ('.$city['department'].')']);
        }

        return $table;
    }

    /**
     * @param array $cities
     *
     * @return Question
     */
    public function getQuestionCities(array $cities)
    {
        $question = new Question('<question>Please enter the index of city :</question> ', '0');
        $question->setValidator(function ($answer) use ($cities) {
            if (!is_numeric($answer) || 0 > $answer || count($cities) <= $answer) {
                throw new BadConfigureException('Your choice is incorrect');
            }

            return $cities[$answer];
        });
        $question->setMaxAttempts(2);

        return $question;
    }

    /**
     * @param OutputInterface $output
     * @param array           $stations
     *
     * @return Table
     */
    public function getTableStations(OutputInterface $output, array $stations)
    {
        $table = new Table($output);
        $table->setHeaders(['', 'Name', 'Code']);
        foreach ($stations as $key => $station) {
            $key = sprintf('<info>%d</info>', $key);
            $table->addRow([$key, $station['name'], $station['code']]);
        }

        return $table;
    }

    /**
     * @param array $stations
     *
     * @return Question
     */
    public function getQuestionStations(array $stations)
    {
        $question = new Question('<question>Please enter the index of station :</question> ', '0');
        $question->setValidator(function ($answer) use ($stations) {
            if (!is_numeric($answer) || 0 > $answer || count($stations) <= $answer) {
                throw new BadConfigureException('Your choice is incorrect');
            }

            return $stations[$answer];
        });
        $question->setMaxAttempts(2);

        return $question;
    }

    /**
     * @param array  $configurations
     * @param string $station
     *
     * @return Question
     */
    public function getQuestionLineLoadStation(array $configurations, $station)
    {
        $question = new Question('<question>Please enter configuration name of station to '.$station.' :</question> ');
        $question->setValidator(function ($answer) use ($configurations) {
            if (!$this->configSrv->isConfiguration($answer, $configurations)) {
                throw new BadConfigureException('This configuration doesn\'t exists');
            }

            return $answer;
        });
        $question->setMaxAttempts(3);

        return $question;
    }

    /**
     * @return Question
     */
    public function getQuestionStops()
    {
        $question = new Question('<question>Please enter the code of line :</question> ', '');
        $question->setValidator(function ($answer) {
            if (empty($answer)) {
                throw new BadConfigureException('Please enter a value');
            }

            return $answer;
        });
        $question->setMaxAttempts(2);

        return $question;
    }

    /**
     * @param OutputInterface $output
     * @param array           $configurations
     * @param bool            $isDelete
     *
     * @return Table
     */
    public function getTableConfigurations(OutputInterface $output, array $configurations, $isDelete)
    {
        $table = new Table($output);
        $headers = ['Name', 'Code'];
        if ($isDelete) {
            array_unshift($headers, '');
        }
        $table->setHeaders($headers);

        if (count($configurations)) {
            foreach ($configurations as $key => $configuration) {
                $values = [$configuration['name'], $configuration['encoded']];
                if ($isDelete) {
                    $key = sprintf('<info>%d</info>', $key);
                    array_unshift($values, $key);
                }
                $table->addRow($values);
            }
        } else {
            $table->addRow([new TableCell('No result', ['colspan' => $isDelete ? 3 : 2])]);
        }

        return $table;
    }

    /**
     * @param array $configurations
     *
     * @return Question
     */
    public function getQuestionConfiguration(array $configurations)
    {
        $question = new Question('<question>Please enter the index of configuration to delete (-1 -> quit) :</question> ', '0');
        $question->setValidator(function ($answer) use ($configurations) {
            if (!is_numeric($answer) || -1 > $answer || count($configurations) <= $answer) {
                throw new BadConfigureException('Your choice is incorrect');
            }
            if ('-1' === $answer) {
                return;
            }

            return $configurations[$answer];
        });
        $question->setMaxAttempts(2);

        return $question;
    }
}
