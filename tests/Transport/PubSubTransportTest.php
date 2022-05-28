<?php
declare(strict_types=1);
namespace CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Tests\Transport;

use CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Tests\Fixtures\DummyMessage;
use CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport\Connection;
use CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport\PubSubReceiver;
use CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport\PubSubSender;
use CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport\PubSubTransport;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

class PubSubTransportTest extends TestCase
{
    use ProphecyTrait;

    public function testItIsATransport(): void
    {
        $transport = $this->buildTransport();

        self::assertInstanceOf(TransportInterface::class, $transport);
    }

    public function testReceivesMessages(): void
    {
        $message = new DummyMessage('Decoded.');

        $receiver = $this->prophesize(PubSubReceiver::class);
        $receiver->get()->willReturn(new \ArrayObject([new Envelope($message)]))->shouldBeCalledOnce();

        $transport = $this->buildTransport(
            null,
            $receiver->reveal(),
        );

        $envelopes = \iterator_to_array($transport->get(), false);
        self::assertNotEmpty($envelopes);
        self::assertInstanceOf(Envelope::class, $envelopes[0]);
        self::assertSame($message, $envelopes[0]->getMessage());
    }

    private function buildTransport(
        PubSubSender $sender = null,
        PubSubReceiver $receiver = null,
        SerializerInterface $serializer = null,
        Connection $connection = null
    ): PubSubTransport {
        $sender     = $sender ?? $this->prophesize(PubSubSender::class)->reveal();
        $receiver   = $receiver ?? $this->prophesize(PubSubReceiver::class)->reveal();
        $serializer = $serializer ?? $this->prophesize(SerializerInterface::class)->reveal();
        $connection = $connection ?? $this->prophesize(Connection::class)->reveal();

        return new PubSubTransport($sender, $receiver, $connection, $serializer, ['clientConfig']);
    }
}
