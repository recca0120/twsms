<?php

namespace Recca0120\TwSMS\Tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Recca0120\TwSMS\TwSMSMessage;

class TwSMSMessageTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testConstruct()
    {
        $message = new TwSMSMessage(
            $content = 'foo'
        );

        $this->assertSame($content, $message->content);
    }

    public function testContent()
    {
        $message = new TwSMSMessage();
        $message->content(
            $content = 'foo'
        );

        $this->assertSame($content, $message->content);
    }

    public function testCreate()
    {
        $message = TwSMSMessage::create(
            $content = 'foo'
        );

        $this->assertSame($content, $message->content);
    }
}
