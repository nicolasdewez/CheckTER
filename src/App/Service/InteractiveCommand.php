<?php

namespace App\Service;

use App\Exception\BadConfigureException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Question\Question;

/**
 * Class InteractiveCommand.
 */
class InteractiveCommand
{
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
}
