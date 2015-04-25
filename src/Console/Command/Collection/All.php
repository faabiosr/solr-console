<?php

namespace Solr\Console\Command\Collection;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use Symfony\Component\Console\Helper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * List all collections.
 */
class All extends Command
{
    protected function configure()
    {
        $this->setName('collection:list')
             ->setDescription('Fetch the names of the collections in the cluster');
    }

    /**
     * Execute list command. Returns the collections Solr.
     *
     * @param Symfony\Component\Console\Input\InputInterface  $input
     * @param Symfony\Component\Console\Output\OutpuInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $result = $this->client
                           ->get('admin/collections?action=LIST&wt=json')
                           ->json();

            if (!isset($result['collections']) || count($result['collections']) === 0) {
                $output->writeln('<fg=yellow>No collections found</fg=yellow>');

                return 0;
            }

            $table = new Helper\Table($output);
            $table->setHeaders(['Collection']);

            foreach ($result['collections'] as $collection) {
                $table->addRow([$collection]);
            }

            $table->render($output);
        } catch (ClientException $e) {
            $output->writeln("<fg=red>{$e->getMessage()}</fg=red>");

            return 1;
        } catch (ConnectException $e) {
            $output->writeln('<fg=red>The connection failed for host</fg=red>');

            return 1;
        }
    }
}
