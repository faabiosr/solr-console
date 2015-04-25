<?php

namespace Solr\Console\Command\Collection;

use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Subscriber\Mock;
use Symfony\Component\Console\Tester\CommandTester;

class RemoveTest extends \PHPUnit_Framework_TestCase
{
    public function testCheckIfAllInstanceOfCollectionCommand()
    {
        $this->assertInstanceOf('\Solr\Console\Command\Collection\Command', new Remove());
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Please define a host or client instance
     */
    public function testShouldThrowExceptionWhenExecuteWithoutHttpClient()
    {
        $tester = new CommandTester(new Remove());
        $tester->execute([]);
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

        $command = new Remove($client);
        $tester  = new CommandTester($command);

        $tester->execute(['name' => 'test']);

        $this->assertRegExp('/The connection failed for host/', $tester->getDisplay());
        $this->assertEquals(1, $tester->getStatusCode());
    }

    public function testShouldFailWhenClientSendNotFoundResponse()
    {
        $data = [
            'error' => [
                'msg' => 'Some error'
            ]
        ];

        $stream = Stream::factory(json_encode($data));
        $response = new Response(404, ['Content-Type' => 'application/json; charset=UTF-8'], $stream);

        $subscriber = new Mock();
        $subscriber->addResponse($response);

        $client = new Client();
        $client->getEmitter()
               ->attach($subscriber);

        $command = new Remove($client);
        $tester  = new CommandTester($command);

        $tester->execute(['name' => 'test']);

        $this->assertRegExp('/Some error/', $tester->getDisplay());
        $this->assertEquals(1, $tester->getStatusCode());
    }

    public function testShouldExecuteCommand()
    {
        $data = [
            'success' => []
        ];
        $stream = Stream::factory(json_encode($data));
        $response = new Response(200, ['Content-Type' => 'application/json; charset=UTF-8'], $stream);

        $subscriber = new Mock();
        $subscriber->addResponse($response);

        $client = new Client();
        $client->getEmitter()
               ->attach($subscriber);

        $command = new Remove($client);
        $tester  = new CommandTester($command);

        $tester->execute(['name' => 'test']);

        $this->assertRegExp('/The collection was deleted/', $tester->getDisplay());
        $this->assertEquals(0, $tester->getStatusCode());
    }
}

