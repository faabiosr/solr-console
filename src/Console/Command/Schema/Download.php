<?php

namespace Solr\Console\Command\Schema;

use Symfony\Component\Console\Input\InputArgument as IArg;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Download extends Command
{
    protected function configure()
    {
        $this->setName('schema:download')
             ->setDescription('Download configuration set');

        $this->addArgument('name', IArg::REQUIRED, 'Config set name')
             ->addArgument('dest', IArg::REQUIRED, 'Destination folder. Where the file is saved. e.g. /tmp');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $dest = $input->getArgument('dest');

        $path = '/configs';

        if (!$this->client->exists($path)) {
            $output->writeln('<fg=red>Configs node not found</fg=red>');

            return 1;
        }

        $path = "{$path}/{$name}";

        if (!$this->client->exists($path)) {
            $output->writeln("<fg=red>Config set {$name} not found</fg=red>");

            return 1;
        }

        $files = $this->client->getChildren($path);

        if (count($files) === 0) {
            $output->writeln("<fg=red>Files not found in config set {$name}</fg=red>");

            return 1;
        }

        $zip = new \ZipArchive();
        $zip->open("{$dest}/{$name}.zip", \ZipArchive::CREATE);

        foreach ($files as $file) {
            $content = $this->client->get("{$path}/{$file}");
            $zip->addFromString($file, $content);
        }

        if ($zip->close()) {
            $output->writeln("<fg=green>The config set {$name} was saved in {$dest}</fg=green>");
        }
    }
}
