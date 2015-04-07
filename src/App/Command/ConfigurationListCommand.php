<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Class ConfigurationListCommand.
 */
class ConfigurationListCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('apisncf:configuration:list')
            ->setDescription('Get list of configurations')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Stations</info>');
        $this->displayTableForType('station', $output);

        $output->writeln(PHP_EOL.'<info>Lines</info>');
        $this->displayTableForType('line', $output);
    }

    /**
     * @param string          $type
     * @param OutputInterface $output
     */
    protected function displayTableForType($type, OutputInterface $output)
    {
        $table = new Table($output);
        $table->setHeaders(['Name', 'Code']);

        $finder = new Finder();
        $finder->files()->in(__DIR__.'/../../../configuration/'.$type)->sort(function (\SplFileInfo $a, \SplFileInfo $b) {
            return strcmp(base64_decode($a->getFilename()), base64_decode($b->getFilename()));
        });

        if ($finder->count()) {
            foreach ($finder as $configuration) {
                $table->addRow([base64_decode($configuration->getFileName()), $configuration->getFileName()]);
            }
        } else {
            $table->addRow([new TableCell('No result', ['colspan' => 2])]);
        }

        $table->render();
    }
}
