<?php

namespace EasyBib\Tests\Api\Client\Resource;

use EasyBib\Api\Client\ApiSession;
use EasyBib\Api\Client\Resource\Collection;
use EasyBib\Api\Client\Resource\Resource;
use EasyBib\Api\Client\ResponseDataContainer;
use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    public function dataProvider()
    {
        return [
            [[
                'data' => [
                    [
                        'links' => [
                            0 => [
                                'title' => 'James',
                                'type' => 'text/html',
                                'href' => 'http://api.example.org/foo/',
                                'ref' => 'foo resource',
                            ],
                        ],
                    ],
                ],
            ]],
        ];
    }

    /**
     * @dataProvider dataProvider
     * @param array $payload
     */
    public function testOffsetExists(array $payload)
    {
        $resourceList = $this->getResourceList(json_encode($payload));
        $this->assertTrue(isset($resourceList[0]));
        $this->assertFalse(isset($resourceList[1]));
    }

    /**
     * @dataProvider dataProvider
     * @param array $payload
     */
    public function testOffsetGet(array $payload)
    {
        $resourceList = $this->getResourceList(json_encode($payload));
        $this->assertInstanceOf(Resource::class, $resourceList[0]);
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage offsetSet() is degenerate
     */
    public function testOffsetSet()
    {
        $resourceList = $this->getResourceList();
        $resourceList->offsetSet(0, (object) []);
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage offsetUnset() is degenerate
     */
    public function testOffsetUnset()
    {
        $resourceList = $this->getResourceList();
        $resourceList->offsetUnset(0);
    }

    /**
     * @param string $body
     * @return Collection
     */
    private function getResourceList($body = '')
    {
        $response = new Response(200);
        $response->setBody($body);

        $container = ResponseDataContainer::fromResponse($response);
        $apiSession = new ApiSession('ABC123', new Client());
        $resourceList = new Collection($container, $apiSession);

        return $resourceList;
    }
}
