<?php

namespace Solr\Console\Command\Schema;

use Symfony\Component\Console\Tester\CommandTester;

class DownloadTest extends \PHPUnit_Framework_TestCase
{
    private $client;

    public function setUp()
    {
        $this->client = new \Zookeeper('127.0.0.1:2181');
    }

    public function tearDown()
    {
        $nodes = [
            '/configs/store/langs/stopwords_en.txt',
            '/configs/store/langs',
            '/configs/store/schema.xml',
            '/configs/store/some',
            '/configs/store',
            '/configs'
        ];

        foreach ($nodes as $node) {
            if ($this->client->exists($node)) {
                $this->client->delete($node);
            }
        }

        $dest = __DIR__ . '/../fixture/store.zip';

        if (file_exists($dest)) {
            unlink($dest);
        }

        unset($this->client);
    }

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

    public function testShouldDownloadSchema()
    {
        $this->client->create('/configs', null, [
            [
                'perms'  => \Zookeeper::PERM_ALL,
                'scheme' => 'world',
                'id'     => 'anyone'
            ]
        ]);

        $command = new Upload();
        $tester  = new CommandTester($command);
        $tester->execute([
            'name'       => 'store',
            'config-dir' => __DIR__ . '/../fixture/schema/conf',
            '--host'     => '127.0.0.1:2181'
        ]);

        $command = new Download();
        $tester  = new CommandTester($command);
        $dest    = __DIR__ . '/../fixture';
        $tester->execute([
            'name'   => 'store',
            'dest'   => $dest,
            '--host' => '127.0.0.1:2181'
        ]);

        $this->assertRegExp("/The config set store was saved/", $tester->getDisplay());
        $this->assertEquals(0, $tester->getStatusCode());
    }
}
