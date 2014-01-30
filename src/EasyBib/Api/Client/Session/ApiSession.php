<?php

namespace EasyBib\Api\Client\Session;

use EasyBib\Api\Client\Session\TokenResponse\TokenResponseInterface;
use EasyBib\Api\Client\TokenStore\TokenStoreInterface;
use fkooman\Guzzle\Plugin\BearerAuth\BearerAuth;
use Guzzle\Http\ClientInterface;

class ApiSession
{
    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var \EasyBib\Api\Client\TokenStore\TokenStoreInterface
     */
    private $tokenStore;

    /**
     * @var \Guzzle\Http\ClientInterface
     */
    private $httpClient;

    /**
     * @param string $baseUrl
     * @param TokenStoreInterface $tokenStore
     * @param ClientInterface $httpClient
     */
    public function __construct($baseUrl, TokenStoreInterface $tokenStore, ClientInterface $httpClient)
    {
        $this->baseUrl = $baseUrl;
        $this->tokenStore = $tokenStore;
        $this->httpClient = $httpClient;
    }

//    public function handleAuthorizationResponse(AuthorizationRepsonseInterface $authorizationRepsonse)
//    {
//        $tokenRequest = new AccessTokenRequest($this, $authorizationRepsonse);
//        $this->handleIncomingToken($tokenRequest->send());
//    }

    /**
     * @param TokenResponseInterface $tokenRequest
     */
    public function handleIncomingToken(TokenResponseInterface $tokenRequest)
    {
        $token = $tokenRequest->getToken();
        $this->tokenStore->setToken($token);
        $this->pushTokenToHttpClient($token);
    }

    /**
     * @param RedirectorInterface $redirector
     */
    public function ensureToken(RedirectorInterface $redirector)
    {
        // TODO handle expired token
        $token = $this->tokenStore->getToken();

        if (!$token) {
            $redirector->redirect($this->getAuthorizeUrl());
        }

        $this->pushTokenToHttpClient($token);
    }

    /**
     * @return string
     */
    private function getAuthorizeUrl()
    {
        return $this->baseUrl . '/authorize';
    }

    /**
     * @param $token
     */
    private function pushTokenToHttpClient($token)
    {
        $this->httpClient->addSubscriber(new BearerAuth($token));
    }
}
