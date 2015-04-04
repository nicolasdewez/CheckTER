<?php

namespace App\Command;

use App\Service\ClientSncf;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class LineFindCommand.
 */
class LineFindCommand extends BaseCommand
{
    /** @var ClientSncf */
    protected $clientSncf;

    /**
     * @param ClientSncf $clientSncf
     */
    public function __construct(ClientSncf $clientSncf)
    {
        $this->clientSncf = $clientSncf;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('apisncf:line:find')
            ->setDescription('Get line')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function interactive(InputInterface $input, OutputInterface $output)
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function displayResult(OutputInterface $output)
    {
    }
}
