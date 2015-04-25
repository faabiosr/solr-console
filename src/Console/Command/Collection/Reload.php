<?php

namespace Solr\Console\Command\Collection;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use Symfony\Component\Console\Input\InputArgument as IArg;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Reload collection.
 */
class Reload extends Command
{
    protected function configure()
    {
        $this->setName('collection:reload')
             ->setDescription('Used when you have changed a configuration in ZooKeeper.');

        $this->addArgument('name', IArg::REQUIRED, 'Collection name');
    }

    /**
     * Execute reload command. Reload collection.
     *
     * @param Symfony\Component\Console\Input\InputInterface  $input
     * @param Symfony\Component\Console\Output\OutpuInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $name = $input->getArgument('name');
            $result = $this->client
                           ->get("admin/collections?action=RELOAD&name={$name}&wt=json")
                           ->json();

            if (isset($result['success'])) {
                $output->writeln('<fg=green>The collection was reloaded</fg=green>');
            }

            return 0;
        } catch (ClientException $e) {
            $response = $e->getResponse()->json();

            if (isset($response['error']['msg'])) {
                $output->writeln("<fg=red>{$response['error']['msg']}</fg=red>");
            }

            return 1;
        } catch (ConnectException $e) {
            $output->writeln('<fg=red>The connection failed for host</fg=red>');

            return 1;
        }
    }
}
