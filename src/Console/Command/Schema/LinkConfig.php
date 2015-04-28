<?php

namespace Solr\Console\Command\Schema;

use Symfony\Component\Console\Input\InputArgument as IArg;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption as IOpt;
use Symfony\Component\Console\Output\OutputInterface;

class LinkConfig extends Command
{
    protected function configure()
    {
        $this->setName('schema:link')
             ->setDescription('Link a collection to a configuration set');

        $this->addArgument('name', IArg::REQUIRED, 'Collection name')
             ->addOption('config-name', null, IOpt::VALUE_OPTIONAL, 'Name of configurations to use for this collection');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        if (is_null($input->getOption('config-name'))) {
            $input->setOption('config-name', $input->getArgument('name'));
        }

        parent::initialize($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $configName = $input->getOption('config-name');
        $configPath = "/configs/{$configName}";

        if (!$this->client->exists($configPath)) {
            $output->writeln("<fg=red>Config set {$configName} not found</fg=red>");

            return 1;
        }

        $collectionPath = "/collections/{$name}";

        if (!$this->client->exists($collectionPath)) {
            $output->writeln("<fg=red>Collection {$name} not found</fg=red>");

            return 1;
        }

        $value = [
            'configName' => $configName,
        ];

        $this->client->set($collectionPath, json_encode($value));
        $output->writeln("<fg=green>The collection {$name} was linked</fg=green>");
    }
}
