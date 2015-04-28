<?php

namespace Solr\Console\Command\Schema;

use Symfony\Component\Console\Tester\CommandTester;

class UploadTest extends \PHPUnit_Framework_TestCase
{
    public function testCheckIfAllInstanceOfCollectionCommand()
    {
        $this->assertInstanceOf('\Solr\Console\Command\Schema\Command', new Upload());
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Please define a host or Zookeeper client instance
     */
    public function testShouldThrowExceptionWhenExecuteWithoutHttpClient()
    {
        $tester = new CommandTester(new Upload());
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

        $command = new Upload($client);
        $tester  = new CommandTester($command);

        $tester->execute(['name' => 'test', 'config-dir' => './test/conf']);

        $this->assertRegExp('/Configs node not found/', $tester->getDisplay());
        $this->assertEquals(1, $tester->getStatusCode());
    }
}
