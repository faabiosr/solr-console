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
    private $title = 'Solr Management Console';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct($this->title);

        $this->add(new Command\Collection\All());
        $this->add(new Command\Collection\Reload());
        $this->add(new Command\Collection\Remove());
        $this->add(new Command\Collection\Create());
        $this->add(new Command\Schema\All());
        $this->add(new Command\Schema\LinkConfig());
        $this->add(new Command\Schema\Download());
        $this->add(new Command\Schema\Upload());
        $this->add(new Command\Schema\Remove());
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
