<?php

namespace Solr\Console\Command\Collection;

use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Subscriber\Mock;
use Symfony\Component\Console\Tester\CommandTester;

class AllTest extends \PHPUnit_Framework_TestCase
{
    public function testCheckIfAllInstanceOfCollectionCommand()
    {
        $this->assertInstanceOf('\Solr\Console\Command\Collection\Command', new All());
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Please define a host or client instance
     */
    public function testShouldThrowExceptionWhenExecuteWithoutHttpClient()
    {
        $tester = new CommandTester(new All());
        $tester->execute([]);
    }

    public function testExecuteShouldReturnEmptyCollections()
    {
        $stream = Stream::factory(json_encode([]));
        $response = new Response(200, ['Content-Type' => 'application/json; charset=UTF-8'], $stream);

        $subscriber = new Mock();
        $subscriber->addResponse($response);

        $client = new Client();
        $client->getEmitter()
               ->attach($subscriber);

        $command = new All($client);
        $tester  = new CommandTester($command);

        $tester->execute([]);

        $this->assertRegExp('/No collections found/', $tester->getDisplay());
        $this->assertEquals(0, $tester->getStatusCode());
    }

    public function testShouldShowErrorWhenConnectionFail()
    {
        $exception = $this->getMockBuilder('\GuzzleHttp\Exception\ConnectException')
            ->disableOriginalConstructor()
            ->getMock();

        $client  = $this->getMockBuilder('\GuzzleHttp\Client')
            ->setMethods(['get'])
            ->getMock();

        $client->expects($this->once())
            ->method('get')
            ->will($this->throwException($exception));

        $command = new All($client);
        $tester  = new CommandTester($command);

        $tester->execute([]);

        $this->assertRegExp('/The connection failed for host/', $tester->getDisplay());
        $this->assertEquals(1, $tester->getStatusCode());
    }

    public function testShouldFailWhenClientSendNotFoundResponse()
    {
        $stream = Stream::factory(json_encode([]));
        $response = new Response(404, ['Content-Type' => 'application/json; charset=UTF-8'], $stream);

        $subscriber = new Mock();
        $subscriber->addResponse($response);

        $client = new Client();
        $client->getEmitter()
               ->attach($subscriber);

        $command = new All($client);
        $tester  = new CommandTester($command);

        $tester->execute([]);

        $this->assertRegExp('/Client error response/', $tester->getDisplay());
    }

    public function testShouldRetrieveCollectionsTable()
    {
        $data = [
            'collections' => [
               'store',
               'kpi'
            ]
        ];
        $stream = Stream::factory(json_encode($data));
        $response = new Response(200, ['Content-Type' => 'application/json; charset=UTF-8'], $stream);

        $subscriber = new Mock();
        $subscriber->addResponse($response);

        $client = new Client();
        $client->getEmitter()
               ->attach($subscriber);

        $command = new All($client);
        $tester  = new CommandTester($command);

        $tester->execute([]);

        $this->assertRegExp('/Collection/', $tester->getDisplay());
        $this->assertRegExp('/store/', $tester->getDisplay());
        $this->assertEquals(0, $tester->getStatusCode());
    }
}
