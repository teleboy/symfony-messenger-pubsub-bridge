<?php
declare(strict_types=1);

namespace CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport;

use CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport\Config\Dsn;
use Google\Cloud\PubSub\PubSubClient;
use Symfony\Component\Messenger\Exception\InvalidArgumentException;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

class PubSubTransportFactory implements TransportFactoryInterface
{
    private const GOOGLE_CLOUD_PUBSUB_SCHEME = 'pubsub';
    public const GOOGLE_CLOUD_PUBSUB_PROTO_SCHEME = 'pubsub://';

    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        if (!$this->supports($dsn, $options)) {
            throw new InvalidArgumentException(sprintf('Invalid DSN: %s', self::GOOGLE_CLOUD_PUBSUB_SCHEME));
        }

        $dsnObject    = new Dsn($dsn, $options);
        $pubSubClient = new PubSubClient($dsnObject->getClientConfig()->toArray());
        $connection   = new Connection($pubSubClient, $dsnObject->getSubscriptionConfig(), $dsnObject->getTopicConfig());

        return new PubSubTransport(
            new PubSubSender($connection, $serializer),
            new PubSubReceiver($connection, $serializer),
            $connection,
            $serializer
        );
    }

    public function supports(string $dsn, array $options): bool
    {
        return \strpos($dsn, self::GOOGLE_CLOUD_PUBSUB_SCHEME) === 0;
    }
}
