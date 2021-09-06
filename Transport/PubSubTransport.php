<?php

namespace CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport;

use Google\Cloud\PubSub\PubSubClient;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\SetupableTransportInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

class PubSubTransport implements TransportInterface, SetupableTransportInterface
{
    private Connection $connection;

    private SerializerInterface $serializer;

    private PubSubReceiver $receiver;

    private PubSubSender $sender;

    public function __construct(PubSubSender $sender, PubSubReceiver $receiver, Connection $connection, SerializerInterface $serializer)
    {
        $this->sender     = $sender;
        $this->receiver   = $receiver;
        $this->connection = $connection;
        $this->serializer = $serializer;
    }

    public function setup(): void
    {
        $this->connection->setup();
    }

    public function get(): iterable
    {
        return $this->receiver->get();
    }

    public function getSerializer(): SerializerInterface
    {
        return $this->serializer;
    }

    public function ack(Envelope $envelope): void
    {
        $this->receiver->ack($envelope);
    }

    public function reject(Envelope $envelope): void
    {
        $this->receiver->reject($envelope);
    }

    public function send(Envelope $envelope): Envelope
    {
        return $this->sender->send($envelope);
    }

    public function getClient(): PubSubClient
    {
        return $this->connection->getClient();
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }
}
