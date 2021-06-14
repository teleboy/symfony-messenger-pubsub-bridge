<?php
declare(strict_types=1);

namespace CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport;

use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\Subscription;
use Symfony\Component\Messenger\Stamp\NonSendableStampInterface;

/**
 * Stamp applied when a message is received from Pub/Sub.
 */
class PubSubReceivedStamp implements NonSendableStampInterface
{
    private Message $message;

    private Subscription $subscription;

    public function __construct(Message $message, Subscription $subscription)
    {
        $this->message      = $message;
        $this->subscription = $subscription;
    }

    public function getMessage(): Message
    {
        return $this->message;
    }

    public function getSubscription(): Subscription
    {
        return $this->subscription;
    }
}
