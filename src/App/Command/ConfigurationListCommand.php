<?php

namespace App\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ConfigurationListCommand.
 */
class ConfigurationListCommand extends ContainerCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:configuration:list')
            ->setDescription('Get list of configurations')
            ->addOption('delete', 'd', InputOption::VALUE_NONE, 'Possible to delete configuration');
    }

    /**
     * @param InputInterface $input
     *
     * @return bool
     */
    protected function isDelete(InputInterface $input)
    {
        return $input->getOption('delete');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Stations</info>');
        $this->displayTableForType('station', $input, $output);

        $output->writeln(PHP_EOL.'<info>Lines</info>');
        $this->displayTableForType('line', $input, $output);
    }

    /**
     * @param string          $type
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function displayTableForType($type, InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getHelper('question');
        $serviceConfiguration = $this->getContainer()->get('app.configuration');
        $interactiveCommand = $this->getContainer()->get('app.interactive_command');

        do {
            $configurations = $serviceConfiguration->get($type);
            $table = $interactiveCommand->getTableConfigurations($output, $configurations, $this->isDelete($input));
            $table->render();

            if (count($configurations) && $this->isDelete($input)) {
                $question = $interactiveCommand->getQuestionConfiguration($configurations);
                $configToDelete = $questionHelper->ask($input, $output, $question);
                if (null !== $configToDelete) {
                    $serviceConfiguration->delete($type, $configToDelete);
                }
            }
        } while ($this->isDelete($input) && 1 < count($configurations) && null !== $configToDelete);
    }
}
