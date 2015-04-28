<?php

namespace Solr\Console\Command\Schema;

use Symfony\Component\Console\Tester\CommandTester;

class LinkConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testCheckIfAllInstanceOfCollectionCommand()
    {
        $this->assertInstanceOf('\Solr\Console\Command\Schema\Command', new LinkConfig());
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Please define a host or Zookeeper client instance
     */
    public function testShouldThrowExceptionWhenExecuteWithoutHttpClient()
    {
        $tester = new CommandTester(new LinkConfig());
        $tester->execute([]);
    }

    public function testExecuteShouldRetrieveErrorWhenConfigsSetNotExists()
    {
        $client = $this->getMockBuilder('\Zookeeper')
            ->setMethods(['exists'])
            ->getMock();

        $client->expects($this->once())
            ->method('exists')
            ->will($this->returnValue(false));

        $command = new LinkConfig($client);
        $tester  = new CommandTester($command);

        $tester->execute(['name' => 'test']);

        $this->assertRegExp('/Config set test not found/', $tester->getDisplay());
        $this->assertEquals(1, $tester->getStatusCode());
    }

    public function testExecuteShouldRetrieveErrorWhenCollectionNotExists()
    {
        $client = $this->getMockBuilder('\Zookeeper')
            ->setMethods(['exists'])
            ->getMock();

        $client->expects($this->at(0))
            ->method('exists')
            ->will($this->returnValue(true));

        $client->expects($this->at(1))
            ->method('exists')
            ->will($this->returnValue(false));

        $command = new LinkConfig($client);
        $tester  = new CommandTester($command);

        $tester->execute(['name' => 'test']);

        $this->assertRegExp('/Collection test not found/', $tester->getDisplay());
        $this->assertEquals(1, $tester->getStatusCode());
    }

    public function testShouldExecuteCommand()
    {
        $client = $this->getMockBuilder('\Zookeeper')
            ->setMethods(['exists', 'set'])
            ->getMock();

        $client->expects($this->at(0))
            ->method('exists')
            ->will($this->returnValue(true));

        $client->expects($this->at(1))
            ->method('exists')
            ->will($this->returnValue(true));

        $command = new LinkConfig($client);
        $tester  = new CommandTester($command);

        $tester->execute(['name' => 'test']);

        $this->assertRegExp('/The collection test was linked/', $tester->getDisplay());
        $this->assertEquals(0, $tester->getStatusCode());
    }
}
