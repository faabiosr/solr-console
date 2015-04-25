<?php

namespace Solr\Console;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    public function testCheckIfAllInstanceOfCollectionCommand()
    {
        $this->assertInstanceOf('\Symfony\Component\Console\Application', new Application());
    }

    public function testGetLongVersion()
    {
        $app = new Application();

        $this->assertRegExp('/Solr Management Console/', $app->getLongVersion());
    }

    /**
     * @dataProvider commandProvider
     */
    public function testShouldCheckIfCommandsAreRegistered($name, $class)
    {
        $app = new Application();

        $this->assertInstanceOf($class, $app->get($name));
    }

    public function commandProvider()
    {
        return [
            [
                'collection:list',
                'Solr\Console\Command\Collection\All'
            ]
        ];
    }
}
