<?php

namespace Solr\Console\Command\Collection;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use Symfony\Component\Console\Input\InputArgument as IArg;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption as IOpt;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Create collection.
 */
class Create extends Command
{
    protected function configure()
    {
        $this->setName('collection:create')
             ->setDescription('Create a collection');

        $this->addArgument('name', IArg::REQUIRED, 'Collection name')
             ->addOption('num-shards', null, IOpt::VALUE_OPTIONAL, 'The number of shards to be create as part of the collection', 1)
             ->addOption('replication-factor', null, IOpt::VALUE_OPTIONAL, 'The number of replicas to be created for each shard', 1)
             ->addOption('max-shards-per-node', null, IOpt::VALUE_OPTIONAL, 'Sets a limit on the number of replicas', 1)
             ->addOption('config-name', null, IOpt::VALUE_OPTIONAL, 'Name of configurations to use for this collection');
    }

    /**
     * Initialize command with default config name, when not defined.
     *
     * @param Symfony\Component\Console\Input\InputInterface   $input
     * @param Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        if (is_null($input->getOption('config-name'))) {
            $input->setOption('config-name', $input->getArgument('name'));
        }

        parent::initialize($input, $output);
    }

    /**
     * Execute create command. Creates a collection in Solr.
     *
     * @param Symfony\Component\Console\Input\InputInterface  $input
     * @param Symfony\Component\Console\Output\OutpuInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $url = sprintf('admin/collections?action=CREATE&name=%s&numShards=%d&replicationFactor=%d&maxShardsPerNode=%d&collection.configName=%s&wt=json',
                $input->getArgument('name'),
                $input->getOption('num-shards'),
                $input->getOption('replication-factor'),
                $input->getOption('max-shards-per-node'),
                $input->getOption('config-name')
            );

            $result = $this->client
                           ->get($url)
                           ->json();

            if (isset($result['success'])) {
                $output->writeln('<fg=green>The collection was created</fg=green>');
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
