<?php

namespace Solr\Console\Command\Schema;

use Symfony\Component\Console\Tester\CommandTester;

class DownloadTest extends \PHPUnit_Framework_TestCase
{
    public function testCheckIfAllInstanceOfCollectionCommand()
    {
        $this->assertInstanceOf('\Solr\Console\Command\Schema\Command', new Download());
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Please define a host or Zookeeper client instance
     */
    public function testShouldThrowExceptionWhenExecuteWithoutHttpClient()
    {
        $tester = new CommandTester(new Download());
        $tester->execute([]);
    }

    public function testShouldRetrieveErrorWhenConfigsNodeNotExists()
    {
        $client = $this->getMockBuilder('\Zookeeper')
            ->setMethods(['exists'])
            ->getMock();

        $client->expects($this->once())
            ->method('exists')
            ->will($this->returnValue(false));

        $command = new Download($client);
        $tester  = new CommandTester($command);

        $tester->execute(['name' => 'test', 'dest' => '/tmp']);

        $this->assertRegExp('/Configs node not found/', $tester->getDisplay());
        $this->assertEquals(1, $tester->getStatusCode());
    }

    public function testShouldRetrieveErrorWhenConfigSetNotExists()
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

        $command = new Download($client);
        $tester  = new CommandTester($command);

        $tester->execute(['name' => 'test', 'dest' => '/tmp']);

        $this->assertRegExp('/Config set test not found/', $tester->getDisplay());
        $this->assertEquals(1, $tester->getStatusCode());
    }

    public function testShouldRetrieveErrorWhenNotExistsFilesInConfigSet()
    {
        $client = $this->getMockBuilder('\Zookeeper')
            ->setMethods(['exists', 'getChildren'])
            ->getMock();

        $client->expects($this->at(0))
            ->method('exists')
            ->will($this->returnValue(true));

        $client->expects($this->at(1))
            ->method('exists')
            ->will($this->returnValue(true));

        $client->expects($this->once())
            ->method('getChildren')
            ->will($this->returnValue([]));

        $command = new Download($client);
        $tester  = new CommandTester($command);

        $tester->execute(['name' => 'test', 'dest' => '/tmp']);

        $this->assertRegExp('/Files not found in config set test/', $tester->getDisplay());
        $this->assertEquals(1, $tester->getStatusCode());
    }
}
