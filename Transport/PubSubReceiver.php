<?php
declare(strict_types=1);

namespace CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport;

use Google\Cloud\PubSub\Subscription;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\LogicException;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class PubSubReceiver implements ReceiverInterface
{
    private SerializerInterface $serializer;

    private Connection $connection;

    public function __construct(Connection $connection, SerializerInterface $serializer = null)
    {
        $this->connection = $connection;
        $this->serializer = $serializer ?? new PhpSerializer();
    }

    /**
     * {@inheritDoc}
     */
    public function get(): iterable
    {
        yield from $this->getEnvelope();
    }

    private function getEnvelope(): iterable
    {
        $pubSubMessage = $this->connection->get();
        if (null === $pubSubMessage) {
            return;
        }

        $body = $pubSubMessage->data();
        $attributes = $pubSubMessage->attributes();

        try {
            $envelope = $this->serializer->decode([
                'body' => $body,
                'headers' => $attributes,
            ]);
        } catch (MessageDecodingFailedException $exception) {

            throw $exception;
        }

        /* The subscription is only set on the message itself when it is obtained via push delivery,
        see the PHP doc of Google\Cloud\PubSub\Message::subscription().
        So we use the subscription which the connection uses as fallback */
        $subscription = $pubSubMessage->subscription() instanceof Subscription ? $pubSubMessage->subscription() : $this->connection->getSubscription();

        yield $envelope->with(new PubSubReceivedStamp($pubSubMessage, $subscription));
    }

    /**
     * {@inheritDoc}
     */
    public function ack(Envelope $envelope): void
    {
        $stamp = $this->findPubSubStamp($envelope);

        $this->connection->ack($stamp->getMessage(), $stamp->getSubscription());
    }

    private function findPubSubStamp(Envelope $envelope): PubSubReceivedStamp
    {
        $pubSubReceivedStamp = $envelope->last(PubSubReceivedStamp::class);
        if (!$pubSubReceivedStamp instanceof PubSubReceivedStamp) {
            throw new LogicException('No "PubSubReceivedStamp" stamp found on the Envelope.');
        }

        return $pubSubReceivedStamp;
    }

    /**
     * {@inheritDoc}
     */
    public function reject(Envelope $envelope): void
    {
        $stamp = $this->findPubSubStamp($envelope);

        $this->connection->nack($stamp->getMessage(), $stamp->getSubscription());
    }
}
