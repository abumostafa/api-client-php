<?php

namespace EasyBib\Tests\Api\Client;

use EasyBib\Api\Client\ApiBuilder;
use EasyBib\Api\Client\Resource\Resource;
use EasyBib\OAuth2\Client\TokenStore;
use EasyBib\Tests\Mocks\OAuth2\Client\ExceptionMockRedirector;
use EasyBib\Tests\Mocks\OAuth2\Client\MockRedirectException;
use Guzzle\Http\Client;
use Guzzle\Plugin\History\HistoryPlugin;
use Guzzle\Plugin\Mock\MockPlugin;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class ApiBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Given
     */
    protected $given;

    /**
     * @var string
     */
    protected $apiBaseUrl = 'http://data.easybib.example.com';

    /**
     * @var HistoryPlugin
     */
    protected $history;

    /**
     * @var Client
     */
    protected $apiHttpClient;

    /**
     * @var Client
     */
    protected $oauthHttpClient;

    /**
     * @var ApiTraverser
     */
    protected $api;

    /**
     * @var MockPlugin
     */
    protected $apiMockResponses;

    /**
     * @var MockPlugin
     */
    protected $oauthMockResponses;

    /**
     * @var TokenStore
     */
    protected $tokenStore;

    /**
     * @var ApiBuilder
     */
    protected $builder;

    public function setUp()
    {
        parent::setUp();

        $this->given = new Given();

        $this->apiHttpClient = new Client($this->apiBaseUrl);
        $this->apiMockResponses = new MockPlugin();
        $this->history = new HistoryPlugin();
        $this->apiHttpClient->addSubscriber($this->apiMockResponses);
        $this->apiHttpClient->addSubscriber($this->history);

        $this->oauthMockResponses = new MockPlugin();

        $this->oauthHttpClient = new Client($this->apiBaseUrl);
        $this->oauthHttpClient->addSubscriber(new HistoryPlugin());
        $this->oauthHttpClient->addSubscriber($this->oauthMockResponses);

        $this->tokenStore = new TokenStore(new Session(new MockArraySessionStorage()));

        $this->builder = new ApiBuilder(new ExceptionMockRedirector());

        $this->builder->setOauthHttpClient($this->oauthHttpClient);
        $this->builder->setApiHttpClient($this->apiHttpClient);
        $this->builder->setTokenStore($this->tokenStore);
    }

    public function testAuthorizationCodeGrant()
    {
        $api = $this->builder->createWithAuthorizationCodeGrant([
            'client_id' => 'ABC123',
            'redirect_url' => 'http://foo.example.com/handle-auth-code',
        ]);

        $this->setExpectedException(MockRedirectException::class);

        $api->getUser();
    }

    public function testJsonWebTokenGrant()
    {
        $api = $this->builder->createWithJsonWebTokenGrant([
            'client_id' => 'ABC123',
            'client_secret' => 'XYZ987',
            'user_id' => 'user_456',
        ]);

        $this->given->iAmReadyToRespondWithAToken($this->oauthMockResponses);
        $this->given->iAmReadyToRespondWithAResource(
            $this->apiMockResponses,
            ['data' => ['foo' => 'bar']]
        );

        $this->assertInstanceOf(Resource::class, $api->getUser());
    }
}
