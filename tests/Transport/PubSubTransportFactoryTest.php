<?php
namespace CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Tests\Transport;

use CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport\Config\Dsn;
use CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport\Connection;
use CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport\PubSubReceiver;
use CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport\PubSubSender;
use CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport\PubSubTransport;
use CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport\PubSubTransportFactory;
use Google\Cloud\PubSub\PubSubClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class PubSubTransportFactoryTest extends TestCase
{
    public function testSupports_onlyPubSubTransports(): void
    {
        $factory = new PubSubTransportFactory();

        self::assertTrue($factory->supports('pubsub://my-project/my-topic?subscription=foo', []));
        self::assertFalse($factory->supports('sqs://localhost', []));
        self::assertFalse($factory->supports('invalid-dsn', []));
    }

    public function testCreateTransport(): void
    {
        $factory    = new PubSubTransportFactory();
        $serializer = $this->createMock(SerializerInterface::class);

        $dsn          = new Dsn('pubsub://my-project/my-topic?subscription=foo', ['host' => 'localhost']);
        $pubSubClient = new PubSubClient($dsn->getClientConfig()->toArray());
        $connection   = new Connection($pubSubClient, $dsn->getSubscriptionConfig(), $dsn->getTopicConfig());

        $expectedTransport = new PubSubTransport(
            new PubSubSender($connection, $serializer),
            new PubSubReceiver($connection, $serializer),
            $connection,
            $serializer,
            $dsn->getClientConfig()->toArray(),
        );

        self::assertEquals($expectedTransport, $factory->createTransport('pubsub://my-project/my-topic?subscription=foo', ['host' => 'localhost'], $serializer));
    }
}
