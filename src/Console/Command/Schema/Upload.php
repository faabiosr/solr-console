<?php

namespace Solr\Console\Command\Schema;

use Symfony\Component\Console\Input\InputArgument as IArg;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class Upload extends Command
{
    protected function configure()
    {
        $this->setName('schema:upload')
             ->setDescription('Upload configuration set');

        $this->addArgument('name', IArg::REQUIRED, 'Config set name')
             ->addArgument('config-dir', IArg::REQUIRED, 'Config set folder. e.g /schema/conf');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $configDir = $input->getArgument('config-dir');

        $path = '/configs';

        if (!$this->client->exists($path)) {
            $this->createFile($path);
        }

        $path = "{$path}/{$name}";

        $this->createFile($path);

        $finder = new Finder();

        foreach ($finder->in($configDir) as $file) {
            if ($file->isDir()) {
                $this->createFile("{$path}/{$file->getRelativePathname()}");
            }

            $this->createFile("{$path}/{$file->getRelativePathname()}", $file->getContents());
        }

        $output->writeln("<fg=green>The config set {$name} was uploaded</fg=green>");
    }

    /**
     * Create file or dir into ZooKeeper.
     *
     * @param string $filePath
     * @param string $value
     */
    private function createFile($filePath, $value = null)
    {
        if (!$this->client->exists($filePath)) {
            $this->client->create($filePath, $value, [
                [
                    'perms' => \Zookeeper::PERM_ALL,
                    'scheme' => 'world',
                    'id' => 'anyone',
                ],
            ]);
        }

        $this->client->set($filePath, $value);
    }
}
