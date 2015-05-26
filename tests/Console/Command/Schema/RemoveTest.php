<?php

namespace Solr\Console\Command\Schema;

use Symfony\Component\Console\Tester\CommandTester;

class RemoveTest extends \PHPUnit_Framework_TestCase
{
    private $client;

    public function setUp()
    {
        $this->client = new \Zookeeper('127.0.0.1:2181');
    }

    public function tearDown()
    {
        $nodes = [
            '/collections/store',
            '/collections/test',
            '/collections',
        ];

        foreach ($nodes as $node) {
            if ($this->client->exists($node)) {
                $this->client->delete($node);
            }
        }

        unset($this->client);
    }

    public function testShouldThrowErrorWhenConfigSetNotExists()
    {
        $client = $this->getMockBuilder('\Zookeeper')
            ->setMethods(['exists'])
            ->getMock();

        $client->expects($this->once())
            ->method('exists')
            ->will($this->returnValue(false));

        $tester = new CommandTester(
            new Remove($client)
        );

        $tester->execute(['name' => 'test']);

        $this->assertRegExp('/Configs node not found/', $tester->getDisplay());
        $this->assertEquals(1, $tester->getStatusCode());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Config set name must be string
     */
    public function testShouldThrowExceptionWhenSetInvalidConfigSetNameType()
    {
        $client = $this->getMockBuilder('\Zookeeper')
            ->setMethods(['exists'])
            ->getMock();

        $client->expects($this->once())
            ->method('exists')
            ->will($this->returnValue(true));

        $tester = new CommandTester(
            new Remove($client)
        );

        $tester->execute(['name' => 10]);
    }

    private function createCollection($name, $configSet)
    {
        if (!$this->client->exists('/collections')) {
            $this->client->create('/collections', null, [
                [
                    'perms'  => \Zookeeper::PERM_ALL,
                    'scheme' => 'world',
                    'id'     => 'anyone'
                ]
            ]);
        }

        $value = json_encode([
            'configName' => $configSet
        ]);

        $this->client->create('/collections/' . $name, $value, [
            [
                'perms'  => \Zookeeper::PERM_ALL,
                'scheme' => 'world',
                'id'     => 'anyone'
            ]
        ]);
    }

    public function testShouldThrowErrorWhenConfigSetWasLinked()
    {
        $tester  = new CommandTester(new Upload($this->client));
        $tester->execute([
            'name'       => 'store',
            'config-dir' => __DIR__ . '/../fixture/schema/conf',
        ]);

        $this->createCollection('store', 'store');
        $this->createCollection('test', 'store');

        $tester = new CommandTester(
            new Remove($this->client)
        );

        $tester->execute(['name' => 'store']);

        $this->assertRegExp('/Config set was linked with other collections/', $tester->getDisplay());
        $this->assertEquals(1, $tester->getStatusCode());

    }

    public function testShouldRemoveSchemaWithoutCollectionNode()
    {
        $tester  = new CommandTester(new Upload($this->client));
        $tester->execute([
            'name'       => 'store',
            'config-dir' => __DIR__ . '/../fixture/schema/conf',
        ]);

        $tester = new CommandTester(new Remove($this->client));
        $tester->execute([
            'name'   => 'store',
        ]);

        $this->assertRegExp('/The config set store was deleted/', $tester->getDisplay());
        $this->assertEquals(0, $tester->getStatusCode());
        $this->assertFalse((bool) $this->client->exists('/configs/store/schema.xml'));
    }

    public function testShouldRemoveSchema()
    {
        $tester  = new CommandTester(new Upload($this->client));
        $tester->execute([
            'name'       => 'store',
            'config-dir' => __DIR__ . '/../fixture/schema/conf',
        ]);

        $tester = new CommandTester(new Remove($this->client));
        $tester->execute([
            'name'   => 'store',
        ]);

        $this->assertRegExp('/The config set store was deleted/', $tester->getDisplay());
        $this->assertEquals(0, $tester->getStatusCode());
        $this->assertFalse((bool) $this->client->exists('/configs/store/schema.xml'));
    }
}
