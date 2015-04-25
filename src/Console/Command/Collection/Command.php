<?php

namespace Solr\Console\Command\Collection;

use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption as IOpt;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Abstract command for collections command.
 */
abstract class Command extends BaseCommand
{
    /**
     * @var GuzzleHttp\Client
     */
    protected $client;

    /**
     * Constructor.
     *
     * @param GuzzleHttp\Client $client
     */
    public function __construct(Client $client = null)
    {
        parent::__construct();

        $this->client = $client;

        $this->addOption('host', null, IOpt::VALUE_REQUIRED, 'Solr Host')
             ->addOption('port', null, IOpt::VALUE_OPTIONAL, 'Solr Port', 8983)
             ->addOption('path', null, IOpt::VALUE_OPTIONAL, 'Solr Path', '/solr');
    }

    /**
     * Initialize command with custom client.
     *
     * @param Symfony\Component\Console\Input\InputInterface  $input
     * @param Symfony\Component\Console\Output\OutpuInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $host = $input->getOption('host');

        if (is_null($host) && !$this->client instanceof Client) {
            throw new \RuntimeException('Please define a host or client instance');
        }

        if (!is_null($host)) {
            $port = $input->getOption('port');
            $path = $input->getOption('path');

            $baseUrl = sprintf('http://%s:%d%s/', $host, $port, $path);

            $this->client = new Client(['base_url' => $baseUrl]);
        }
    }
}
