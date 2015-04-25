<?php

namespace Solr\Console;

use Symfony\Component\Console;

/**
 * An Application is the container for a collection of commands.
 */
class Application extends Console\Application
{
    /**
     * @var string
     */
    private $name = 'Solr Management Console';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct($this->name);

        $this->add(new Command\Collection\All());
        $this->add(new Command\Collection\Reload());
        $this->add(new Command\Collection\Remove());
        $this->add(new Command\Collection\Create());
    }

    /**
     * Get application title.
     *
     * @return string
     */
    public function getLongVersion()
    {
        return sprintf('<info>%s</info>', $this->getName());
    }
}
