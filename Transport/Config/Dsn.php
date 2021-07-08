<?php
declare(strict_types=1);

namespace CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport\Config;

use CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport\PubSubTransportFactory;
use Symfony\Component\Messenger\Exception\InvalidArgumentException;

class Dsn
{
    private ClientConfig $clientConfig;

    private TopicConfig $topicConfig;

    private SubscriptionConfig $subscriptionConfig;

    public function __construct(string $dsn, array $options = [])
    {
        $urlParts = \parse_url($dsn);

        if (false === $urlParts) {
            // It may be a valid URI that parse_url() cannot handle when you want to pass all parameters as options
            if ($dsn !== PubSubTransportFactory::GOOGLE_CLOUD_PUBSUB_PROTO_SCHEME) {
                throw new InvalidArgumentException(sprintf('The given PubSub DSN "%s" is invalid.', $dsn));
            }

            $urlParts = [];
        }

        $pathParts = isset($urlParts['path']) ? \explode('/', \trim($urlParts['path'], '/')) : [];
        \parse_str($urlParts['query'] ?? '', $queryParts);

        if (isset($options['client']) && \is_array($options['client'])) {
            $clientOptions = $options['client'];
        } else {
            $clientOptions = [];
        }

        $this->clientConfig       = $this->createClientConfig($urlParts, $queryParts, $clientOptions);
        $this->topicConfig        = $this->createTopicConfig($pathParts);
        $this->subscriptionConfig = $this->createSubscriptionConfig($queryParts);
    }

    private function createClientConfig(array $urlParts, array $queryParts, array $clientOptions): ClientConfig
    {
        $config = \array_merge(
            ['projectId' => $urlParts['host'] ?? null],
            $queryParts,
            $clientOptions
        );

        return ClientConfig::fromArray($config);
    }

    public function getClientConfig(): ClientConfig
    {
        return $this->clientConfig;
    }

    private function createTopicConfig(array $pathParts): TopicConfig
    {
        $topicName = $pathParts[0] ?? null;

        if ($topicName === null) {
            throw new InvalidArgumentException('You need to supply a topic name');
        }

        return new TopicConfig($topicName);
    }

    public function getTopicConfig(): TopicConfig
    {
        return $this->topicConfig;
    }

    private function createSubscriptionConfig(array $queryParts): SubscriptionConfig
    {
        $subscriptionName = $queryParts['subscription'] ?? null;
        if (empty($subscriptionName) || !\is_string($subscriptionName)) {
            throw new InvalidArgumentException('You need to supply a subscription name');
        }

        return new SubscriptionConfig($subscriptionName);
    }

    public function getSubscriptionConfig(): SubscriptionConfig
    {
        return $this->subscriptionConfig;
    }
}
