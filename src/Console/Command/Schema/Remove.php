<?php

namespace Solr\Console\Command\Schema;

use Symfony\Component\Console\Input\InputArgument as IArg;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Remove extends Command
{
    protected function configure()
    {
        $this->setName('schema:delete')
             ->setDescription('Remove configuration set');

        $this->addArgument('name', IArg::REQUIRED, 'Config set name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');

        $path = '/configs/'.$name;

        if (!$this->client->exists($path)) {
            $output->writeln('<fg=red>Configs node not found</fg=red>');

            return 1;
        }

        if ($this->isLinked($name)) {
            $output->writeln('<fg=red>Config set was linked with other collections</fg=red>');

            return 1;
        }

        $this->recursiveDelete($path);

        $output->writeln("<fg=green>The config set {$name} was deleted</fg=green>");
    }

    private function recursiveDelete($path)
    {
        $children = $this->client->getChildren($path);

        foreach ($children as $child) {
            $this->recursiveDelete($path.'/'.$child);
        }

        $this->client->delete($path);
    }

    private function isLinked($name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException('Config set name must be string');
        }

        $path = '/collections';

        if (!$this->client->exists($path)) {
            return false;
        }

        $collections = $this->client->getChildren($path);

        foreach ($collections as $collection) {
            $config = $this->client->get($path.'/'.$collection);
            $config = json_decode($config, true);

            if (isset($config['configName']) && $config['configName'] === $name) {
                return true;
            }
        }

        return false;
    }
}
