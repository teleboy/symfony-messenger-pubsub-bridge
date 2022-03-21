<?php
declare(strict_types=1);
namespace CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport\Config;

class SubscriptionConfig
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
