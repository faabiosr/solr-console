<?php

namespace Solr\Console\Command\Schema;

use Symfony\Component\Console\Tester\CommandTester;

class UploadTest extends \PHPUnit_Framework_TestCase
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

        unset($this->client);
    }

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

    public function testShouldCreateConfigsNodeIfNotExists()
    {
        $command = new Upload();
        $tester  = new CommandTester($command);
        $tester->execute([
            'name'       => 'store',
            'config-dir' => __DIR__ . '/../fixture/schema/conf',
            '--host'     => '127.0.0.1:2181'
        ]);

        $this->assertRegExp('/The config set store was uploaded/', $tester->getDisplay());
        $this->assertEquals(0, $tester->getStatusCode());
        $this->assertTrue((bool) $this->client->exists('/configs/store/schema.xml'));
    }

    public function testShouldUploadSchema()
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

        $this->assertRegExp('/The config set store was uploaded/', $tester->getDisplay());
        $this->assertEquals(0, $tester->getStatusCode());
        $this->assertTrue((bool) $this->client->exists('/configs/store/schema.xml'));
    }
}
