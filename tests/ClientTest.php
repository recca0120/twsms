<?php

namespace Recca0120\TwSMS\Tests;

use Carbon\Carbon;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Recca0120\TwSMS\Client;

class ClientTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testQuery()
    {
        $client = new Client(
            $username = 'foo',
            $password = 'foo',
            $httpClient = m::mock('Http\Client\HttpClient'),
            $messageFactory = m::mock('Http\Message\MessageFactory')
        );

        $params = [
            'mobile' => 'foo',
            'msgid' => '265078525',
        ];

        $query = array_filter(array_merge([
            'username' => $username,
            'password' => $password,
        ], [
            'mobile' => $params['mobile'],
            'msgid' => $params['msgid'],
        ]));

        $messageFactory->shouldReceive('createRequest')->once()->with(
            'POST',
            'http://api.twsms.com/smsQuery.php',
            ['Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'],
            http_build_query($query)
        )->andReturn(
            $request = m::mock('Psr\Http\Message\RequestInterface')
        );

        $httpClient->shouldReceive('sendRequest')->once()->with($request)->andReturn(
            $response = m::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->shouldReceive('getBody->getContents')->once()->andReturn(
            '<smsResp>
                <code>00000</code>
                <text>Success</text>
                <statuscode></statuscode>
                <statustext></statustext>
                <donetime></donetime>
            </smsResp>'
        );

        $this->assertSame([
            'code' => '00000',
            'text' => 'Success',
        ], $client->query($params));
    }

    public function testCredit()
    {
        $client = new Client(
            $username = 'foo',
            $password = 'foo',
            $httpClient = m::mock('Http\Client\HttpClient'),
            $messageFactory = m::mock('Http\Message\MessageFactory')
        );

        $params = [
            'checkpoint' => 'Y',
        ];

        $query = array_filter(array_merge([
            'username' => $username,
            'password' => $password,
        ], [
            'checkpoint' => $params['checkpoint'],
        ]));

        $messageFactory->shouldReceive('createRequest')->once()->with(
            'POST',
            'http://api.twsms.com/smsQuery.php',
            ['Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'],
            http_build_query($query)
        )->andReturn(
            $request = m::mock('Psr\Http\Message\RequestInterface')
        );

        $httpClient->shouldReceive('sendRequest')->once()->with($request)->andReturn(
            $response = m::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->shouldReceive('getBody->getContents')->once()->andReturn(
            '<smsResp>
                <code>00000</code>
                <text>Success</text>
                <point>6</point>
            </smsResp>'
        );

        $this->assertSame(6, $client->credit());
    }

    public function testSend()
    {
        $client = new Client(
            $username = 'foo',
            $password = 'foo',
            $httpClient = m::mock('Http\Client\HttpClient'),
            $messageFactory = m::mock('Http\Message\MessageFactory')
        );

        $params = [
            'to' => 'foo',
            'text' => 'foo',
        ];

        $query = array_filter(array_merge([
            'username' => $username,
            'password' => $password,
        ], [
            'sendtime ' => empty($params['sendTime']) === false ? Carbon::parse($params['sendTime'])->format('YmdHis') : null,
            'mobile' => $params['to'],
            'message' => $params['text'],
        ]));

        $messageFactory->shouldReceive('createRequest')->once()->with(
            'POST',
            'http://api.twsms.com/smsSend.php',
            ['Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'],
            http_build_query($query)
        )->andReturn(
            $request = m::mock('Psr\Http\Message\RequestInterface')
        );

        $httpClient->shouldReceive('sendRequest')->once()->with($request)->andReturn(
            $response = m::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->shouldReceive('getBody->getContents')->once()->andReturn(
            $content = '
                <smsResp>
                    <code>00000</code>
                    <text>Success</text>
                    <msgid>265078525</msgid>
                </smsResp>
            '
        );

        $this->assertSame([
            'code' => '00000',
            'text' => 'Success',
            'msgid' => '265078525',
        ], $client->send($params));
    }

    /**
     * @expectedException DomainException
     * @expectedExceptionCode 500
     * @expectedExceptionMessage 手機號碼格式錯誤
     */
    public function testSendFail()
    {
        $client = new Client(
            $username = 'foo',
            $password = 'foo',
            $httpClient = m::mock('Http\Client\HttpClient'),
            $messageFactory = m::mock('Http\Message\MessageFactory')
        );
        $params = [
            'to' => 'foo',
            'text' => 'foo',
        ];

        $query = array_filter(array_merge([
            'username' => $username,
            'password' => $password,
        ], [
            'sendtime ' => empty($params['sendTime']) === false ? Carbon::parse($params['sendTime'])->format('YmdHis') : null,
            'mobile' => $params['to'],
            'message' => $params['text'],
        ]));

        $messageFactory->shouldReceive('createRequest')->once()->with(
            'POST',
            'http://api.twsms.com/smsSend.php',
            ['Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'],
            http_build_query($query)
        )->andReturn(
            $request = m::mock('Psr\Http\Message\RequestInterface')
        );

        $httpClient->shouldReceive('sendRequest')->once()->with($request)->andReturn(
            $response = m::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->shouldReceive('getBody->getContents')->once()->andReturn(
            $content = '
                <smsResp>
                    <code>00100</code>
                    <text>mobile tag error</text>
                </smsResp>
            '
        );

        $client->send($params);
    }
}
