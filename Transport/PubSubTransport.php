<?php

namespace CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\SetupableTransportInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

class PubSubTransport implements TransportInterface, SetupableTransportInterface
{
    private Connection $connection;

    private SerializerInterface $serializer;

    private ?PubSubReceiver $receiver = null;

    private ?PubSubSender $sender = null;

    public function __construct(Connection $connection, SerializerInterface $serializer = null)
    {
        $this->connection = $connection;
        $this->serializer = $serializer ?? new PhpSerializer();
    }

    public function setup(): void
    {
        $this->connection->setup();
    }

    public function get(): iterable
    {
        return $this->getReceiver()->get();
    }

    public function getReceiver(): PubSubReceiver
    {
        if (!$this->receiver instanceof PubSubReceiver) {
            $this->receiver = new PubSubReceiver($this->connection, $this->serializer);
        }

        return $this->receiver;
    }

    public function getSerializer(): SerializerInterface
    {
        return $this->serializer;
    }

    public function ack(Envelope $envelope): void
    {
        $this->getReceiver()->ack($envelope);
    }

    public function reject(Envelope $envelope): void
    {
        $this->getReceiver()->reject($envelope);
    }

    public function send(Envelope $envelope): Envelope
    {
        return $this->getSender()->send($envelope);
    }

    private function getSender(): PubSubSender
    {
        if (!$this->sender instanceof PubSubSender) {
            $this->sender = new PubSubSender($this->connection, $this->serializer);
        }

        return $this->sender;
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }
}
