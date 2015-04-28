<?php

namespace Solr\Console\Command\Schema;

use Symfony\Component\Console\Helper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class All extends Command
{
    protected function configure()
    {
        $this->setName('schema:list')
             ->setDescription('Fetch the names of the configs in the cluster');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = '/configs';

        if (!$this->client->exists($path)) {
            $output->writeln('<fg=red>Configs node not found</fg=red>');

            return 1;
        }

        $schemas = $this->client->getChildren($path);

        if (count($schemas) === 0) {
            $output->writeln('<fg=yellow>No schemas found</fg=yellow>');

            return 0;
        }

        $table = new Helper\Table($output);
        $table->setHeaders(['Schema']);

        foreach ($schemas as $schema) {
            $table->addRow([$schema]);
        }

        $table->render($output);
    }
}
