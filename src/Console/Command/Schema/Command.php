<?php

namespace Solr\Console\Command\Schema;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption as IOpt;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Command extends BaseCommand
{
    /**
     * @var \Zookeeper
     */
    protected $client;

    public function __construct(\Zookeeper $client = null)
    {
        parent::__construct();

        $this->client = $client;

        $this->addOption('host', null, IOpt::VALUE_REQUIRED, 'Zookeeper address like csv list of host:port values (e.g. "host1:2181,host2:2181")');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $host = $input->getOption('host');

        if (is_null($host) && !$this->client instanceof \Zookeeper) {
            throw new \RuntimeException('Please define a host or Zookeeper client instance');
        }

        if (is_null($host)) {
            return $this->client;
        }

        $this->client = new \Zookeeper($host);
    }
}
