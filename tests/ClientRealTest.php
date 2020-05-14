<?php

namespace Recca0120\TwSMS\Tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Recca0120\TwSMS\Client;

class ClientRealTest extends TestCase
{
    protected $username = '';

    protected $password = '';

    protected $options = [
        'to' => '',
        'text' => '中文測試',
        'msgid' => '',
    ];

    protected function setUp()
    {
        if (empty($this->username) === true || empty($this->password) === true) {
            $this->markTestSkipped('Please set uid and password');
        }
    }

    protected function tearDown()
    {
        m::close();
    }

    public function testQuery()
    {
        $client = new Client($this->username, $this->password);

        $this->assertInternalType('array', $client->query([
            'mobile' => $this->options['to'],
            'msgid' => $this->options['msgid'],
        ]));
    }

    public function testSend()
    {
        $client = new Client($this->username, $this->password);

        $this->assertInternalType('array', $client->send([
            'to' => $this->options['to'],
            'text' => $this->options['text'],
        ]));
    }
}
