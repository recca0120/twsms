<?php

namespace Recca0120\TwSMS\Tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Recca0120\TwSMS\TwSMSChannel;
use Recca0120\TwSMS\TwSMSMessage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;

class TwSMSChannelTest extends TestCase
{
    protected function setUp()
    {
        if (version_compare(PHP_VERSION, '5.6', '<') === true) {
            $this->markTestSkipped('PHP VERSION must bigger then 5.6');
        }
    }

    protected function tearDown()
    {
        m::close();
    }

    public function testSend()
    {
        $channel = new TwSMSChannel(
            $client = m::mock('Recca0120\TwSMS\Client')
        );

        $client->shouldReceive('send')->with([
            'to' => $to = '+1234567890',
            'text' => $message = 'foo',
        ])->once();

        $channel->send(
            new TestNotifiable(function () use ($to) {
                return $to;
            }),
            new TestNotification(function () use ($message) {
                return $message;
            })
        );
    }

    public function testSendMessage()
    {
        $channel = new TwSMSChannel(
            $client = m::mock('Recca0120\TwSMS\Client')
        );

        $client->shouldReceive('send')->with([
            'to' => $to = '+1234567890',
            'text' => $message = 'foo',
        ])->once();

        $channel->send(
            new TestNotifiable(function () use ($to) {
                return $to;
            }),
            new TestNotification(function () use ($message) {
                return TwSMSMessage::create($message)->subject('subject');
            })
        );
    }

    public function testSendFail()
    {
        $channel = new TwSMSChannel(
            $client = m::mock('Recca0120\TwSMS\Client')
        );

        $channel->send(
            new TestNotifiable(function () {
                return false;
            }),
            new TestNotification(function () {
                return false;
            })
        );
    }
}

if (class_exists(Notification::class) === true) {
    class TestNotifiable
    {
        use Notifiable;

        protected $resolver;

        public function __construct($resolver)
        {
            $this->resolver = $resolver;
        }

        public function routeNotificationForTwSMS()
        {
            $resolver = $this->resolver;

            return $resolver();
        }
    }

    class TestNotification extends Notification
    {
        protected $resolver;

        public function __construct($resolver)
        {
            $this->resolver = $resolver;
        }

        public function toTwSMS()
        {
            $resolver = $this->resolver;

            return $resolver();
        }
    }
}
