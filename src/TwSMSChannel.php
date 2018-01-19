<?php

namespace Recca0120\TwSMS;

use Illuminate\Notifications\Notification;

class TwSMSChannel
{
    /**
     * $client.
     *
     * @var \Recca0120\TwSMS\Client
     */
    protected $client;

    /**
     * __construct.
     *
     * @param \Recca0120\TwSMS\Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     * @return \Recca0120\TwSMS\TwSMSMessage
     */
    public function send($notifiable, Notification $notification)
    {
        if (! $to = $notifiable->routeNotificationFor('TwSMS')) {
            return;
        }

        $message = $notification->toTwSMS($notifiable);

        if (is_string($message)) {
            $message = new TwSMSMessage($message);
        }

        return $this->client->send([
            'to' => $to,
            'text' => trim($message->content),
        ]);
    }
}
