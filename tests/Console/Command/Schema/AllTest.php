<?php

namespace Solr\Console\Command\Schema;

use Symfony\Component\Console\Tester\CommandTester;

class AllTest extends \PHPUnit_Framework_TestCase
{
    public function testCheckIfAllInstanceOfCollectionCommand()
    {
        $this->assertInstanceOf('\Solr\Console\Command\Schema\Command', new All());
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Please define a host or Zookeeper client instance
     */
    public function testShouldThrowExceptionWhenExecuteWithoutHttpClient()
    {
        $tester = new CommandTester(new All());
        $tester->execute([]);
    }

    public function testExecuteShouldRetrieveErrorWhenConfigsNodeNotExists()
    {
        $client = $this->getMockBuilder('\Zookeeper')
            ->setMethods(['exists'])
            ->getMock();

        $client->expects($this->once())
            ->method('exists')
            ->will($this->returnValue(false));

        $command = new All($client);
        $tester  = new CommandTester($command);

        $tester->execute([]);

        $this->assertRegExp('/Configs node not found/', $tester->getDisplay());
        $this->assertEquals(1, $tester->getStatusCode());
    }

    public function testShouldRetrieveEmptyWhenSchemasNotFound()
    {
        $client = $this->getMockBuilder('\Zookeeper')
            ->setMethods(['exists', 'getChildren'])
            ->getMock();

        $client->expects($this->once())
            ->method('exists')
            ->will($this->returnValue(true));

        $client->expects($this->once())
            ->method('getChildren')
            ->will($this->returnValue([]));

        $command = new All($client);
        $tester  = new CommandTester($command);

        $tester->execute([]);

        $this->assertRegExp('/No schemas found/', $tester->getDisplay());
        $this->assertEquals(0, $tester->getStatusCode());
    }

    public function testShouldRetrieveSchemaTable()
    {
        $data = [
            'store',
            'kpi'
        ];

        $client = $this->getMockBuilder('\Zookeeper')
            ->setMethods(['exists', 'getChildren'])
            ->getMock();

        $client->expects($this->once())
            ->method('exists')
            ->will($this->returnValue(true));

        $client->expects($this->once())
            ->method('getChildren')
            ->will($this->returnValue($data));

        $command = new All($client);
        $tester  = new CommandTester($command);

        $tester->execute([]);

        $this->assertRegExp('/Schema/', $tester->getDisplay());
        $this->assertRegExp('/store/', $tester->getDisplay());
        $this->assertEquals(0, $tester->getStatusCode());
    }
}
