<?php
declare(strict_types=1);
namespace CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Sender\SenderInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class PubSubSender implements SenderInterface
{
    private SerializerInterface $serializer;

    private Connection $connection;

    public function __construct(Connection $connection, SerializerInterface $serializer)
    {
        $this->connection = $connection;
        $this->serializer = $serializer;
    }

    public function send(Envelope $envelope): Envelope
    {
        $encodedMessage = $this->serializer->encode($envelope);

        try {
            $publishedMessages = $this->connection->publish($encodedMessage['body'], $encodedMessage['headers'] ?? []);

            if (!isset($publishedMessages['messageIds'][0])) {
                throw new \Exception(\sprintf('Did not receive a message ID after publishing message of type "%s"', \get_class($envelope->getMessage())));
            }

            $id = $publishedMessages['messageIds'][0];
        } catch (\Exception $exception) {
            throw new TransportException($exception->getMessage(), 0, $exception);
        }

        return $envelope->with(new TransportMessageIdStamp($id));
    }
}
