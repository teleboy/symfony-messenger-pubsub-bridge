<?php

namespace CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Tests\Transport;

use CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport\Config\Dsn;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DsnTest extends TestCase
{
    public function testItCannotBeConstructedWithAWrongDsn(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The given PubSub DSN "pubsub://:" is invalid.');

        new Dsn('pubsub://:');
    }

    public function testCanBeConstructedWithEmptyHost(): void
    {
        $dsn = new Dsn('pubsub://auto/my-topic?subscription=foo');

        self::assertEquals('auto', $dsn->getClientConfig()->toArray()['projectId']);
    }

    public function testDSNCanBeOverriddenByOptions(): void
    {
        $dsn = new Dsn('pubsub://auto/my-topic?subscription=foo', [
            'client' => [
                'projectId' => 'my-other-project',
            ]
        ]);

        self::assertEquals('my-other-project', $dsn->getClientConfig()->toArray()['projectId']);
    }
}
