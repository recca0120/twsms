<?php

namespace Recca0120\TwSMS;

use Carbon\Carbon;
use DomainException;
use Http\Client\HttpClient;
use Http\Message\MessageFactory;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;

class Client
{
    /**
     * $apiEndpoint.
     *
     * @var string
     */
    public $apiEndpoint = 'http://api.twsms.com/';

    /**
     * $username.
     *
     * @var string
     */
    protected $username;

    /**
     * $password.
     *
     * @var string
     */
    protected $password;

    /**
     * $httpClient.
     *
     * @var \Http\Client\HttpClient
     */
    protected $httpClient;

    /**
     * $messageFactory.
     *
     * @var \Http\Message\MessageFactory
     */
    protected $messageFactory;

    /**
     * __construct.
     *
     * @param string $username
     * @param string $password
     * @param \Http\Client\HttpClient $httpClient
     * @param \Http\Message\MessageFactory $messageFactory
     */
    public function __construct($username, $password, HttpClient $httpClient = null, MessageFactory $messageFactory = null)
    {
        $this->username = $username;
        $this->password = $password;
        $this->httpClient = $httpClient ?: HttpClientDiscovery::find();
        $this->messageFactory = $messageFactory ?: MessageFactoryDiscovery::find();
    }

    /**
     * query.
     *
     * @param array $params
     * @return array
     */
    public function query($params)
    {
        $response = $this->doRequest('smsQuery.php', array_filter(array_merge([
            'username' => $this->username,
            'password' => $this->password,
            'deltime' => null,
            'checkpoint' => null,
            'mobile' => null,
            'msgid' => null,
            'outrange' => null,
        ], $this->remapParams($params))));

        $response = $this->parseResponse($response);

        if ($this->isValidResponse($response) === false) {
            throw new DomainException(
                empty($response['text']) === false ? $response['text'] : 'Unknown',
                500
            );
        }

        return $response;
    }

    /**
     * send.
     *
     * @param array $params
     * @return string
     */
    public function send($params)
    {
        $response = $this->doRequest('smsSend.php', array_filter(array_merge([
            'username' => $this->username,
            'password' => $this->password,
            'sendtime ' => null,
            'expirytime' => null,
            'popup' => null,
            'mo' => null,
            'mobile' => '',
            'longsms' => null,
            'message' => '',
            'drurl' => null,
        ], $this->remapParams($params))));

        $response = $this->parseResponse($response);

        if ($this->isValidResponse($response) === false) {
            throw new DomainException(
                empty($response['text']) === false ? $response['text'] : 'Unknown',
                500
            );
        }

        return $response;
    }

    /**
     * isValidResponse.
     *
     * @param string $response
     *
     * @return bool
     */
    protected function isValidResponse($response)
    {
        if (empty($response['code']) === true) {
            return false;
        }

        return in_array($response['code'], ['00000', '00001'], true) === true;
    }

    /**
     * doRequest.
     *
     * @param string $uri
     * @param array $params
     *
     * @return string
     */
    protected function doRequest($uri, $params)
    {
        $request = $this->messageFactory->createRequest(
            'POST',
            rtrim($this->apiEndpoint, '/').'/'.$uri,
            ['Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'],
            http_build_query($params)
        );
        $response = $this->httpClient->sendRequest($request);

        return $response->getBody()->getContents();
    }

    /**
     * remapParams.
     *
     * @param array $params
     * @return array
     */
    protected function remapParams($params)
    {
        if (empty($params['to']) === false) {
            $params['mobile'] = $params['to'];
            unset($params['to']);
        }

        if (empty($params['text']) === false) {
            $params['message'] = $params['text'];
            unset($params['text']);
        }

        if (empty($params['sendTime']) === false) {
            $params['sendtime'] = empty($params['sendTime']) === false ? Carbon::parse($params['sendTime'])->format('YmdHis') : null;
            unset($params['sendTime']);
        }

        return $params;
    }

    /**
     * parseResponse.
     *
     * @param string $response
     * @return array
     */
    protected function parseResponse($response)
    {
        $tags = [
            'code',
            'text',
            'msgid',
            'mobile',
            'statuscode',
            'statustext',
            'donetime',
            'username',
            'point',
        ];

        $result = [];

        foreach ($tags as $tag) {
            if ((bool) preg_match('/<'.$tag.'>(?P<value>.+)<\/'.$tag.'>/', $response, $match) === false) {
                continue;
            }

            $result[$tag] = html_entity_decode($match['value']);
        }

        return $result;
    }
}
