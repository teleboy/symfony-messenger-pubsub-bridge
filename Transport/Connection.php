<?php

namespace CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport;

use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\Subscription;
use Google\Cloud\PubSub\Topic;
use Symfony\Component\Messenger\Exception\InvalidArgumentException;

class Connection
{
    private const CLIENT_OPTIONS = [
        'apiEndpoint',
        'projectId',
        'keyFilePath',
        'requestTimeout',
        'retries',
        'scopes',
        'quotaProject',
        'transport'
    ];

    private array $clientConfig;

    private array $subscriptionConfig;

    private array $topicConfig;

    private ?PubSubClient $client = null;

    private ?Subscription $subscription = null;

    public function __construct(array $clientConfig, array $subscriptionConfig, array $topicOptions)
    {
        $this->clientConfig       = $clientConfig;
        $this->topicConfig        = $topicOptions;
        $this->subscriptionConfig = $subscriptionConfig;
    }

    public static function fromDsn(string $dsn, array $options = []): self
    {
        if (false === $parsedUrl = parse_url($dsn)) {
            // this is a valid URI that parse_url cannot handle when you want to pass all parameters as options
            if ($dsn !== PubSubTransportFactory::GOOGLE_CLOUD_PUBSUB_PROTO_SCHEME) {
                throw new InvalidArgumentException(sprintf('The given PubSub DSN "%s" is invalid.', $dsn));
            }

            $parsedUrl = [];
        }

        $options += [
            'client' => []
        ];

        $clientOptions = [
            'projectId' => $parsedUrl['host'] ?? null,
        ];

        $pathParts = isset($parsedUrl['path']) ? explode('/', trim($parsedUrl['path'], '/')) : [];
        $topicName = $pathParts[0] ?? '';

        if ($topicName === '') {
            throw new InvalidArgumentException('You need to supply a topic name');
        }

        $topicOptions = [
            'name' => $topicName ?? null,
        ];

        parse_str($parsedUrl['query'] ?? '', $parsedQuery);

        // client options from DSN query parts
        foreach (self::CLIENT_OPTIONS as $option => $optionValue) {
            if (isset($parsedQuery[$option])) {
                $clientOptions[$option] = $parsedQuery[$optionValue];
            }
        }

        if (is_array($options['client'])) {
            foreach ($options['client'] as $clientOption => $value) {
                $clientOptions[$clientOption] = $value;
            }
        }

        $subscriptionName = $parsedQuery['subscription'] ?? null;
        if ($subscriptionName === null || $subscriptionName === '') {
            throw new InvalidArgumentException('You need to supply a subscription name');
        }

        $subscriptionConfig = [
            'name' => $subscriptionName,
        ];

        return new self($clientOptions, $subscriptionConfig, $topicOptions);
    }

    /**
     * @param string $body
     * @param array $headers
     *
     * @return array
     */
    public function publish(string $body, array $headers = []): array
    {
        return $this->publishOnTopic(
            $this->topic(),
            $body,
            $headers
        );
    }

    private function publishOnTopic(Topic $topic, string $body, array $headers): array
    {
        return $topic->publish(new Message([
            'attributes' => $headers,
            'data' => $body,
        ]));
    }

    private function topic(): Topic
    {
        $pubSub = $this->getClient();

        return $pubSub->topic($this->topicConfig['name']);
    }

    public function get(): ?Message
    {
        $messages = $this->getSubscription()->pull(['maxMessages' => 1]);

        return $messages[0] ?? null;
    }

    public function getSubscription(): Subscription
    {
        if (!$this->subscription instanceof Subscription) {
            $pubSub = $this->getClient();

            $this->subscription = $pubSub->subscription(
                    $this->subscriptionConfig['name'],
                    $this->topicConfig['name']
            );
        }

        return $this->subscription;
    }

    public function ack(Message $message, Subscription $subscription): void
    {
        $subscription->acknowledge($message);
    }

    public function setup(): void
    {
        $pubSub = $this->getClient();

        $topicName = $this->topicConfig['name'];
        if (!$pubSub->topic($topicName)->exists()) {
            $pubSub->topic($topicName)->create();
        }

        $subscriptionName = $this->subscriptionConfig['name'];
        if (!$pubSub->subscription($subscriptionName, $topicName)->exists()) {
            $pubSub->subscription($subscriptionName, $topicName)->create();
        }
    }

    private function getClient(): PubSubClient
    {
        if (!$this->client instanceof PubSubClient) {
            $this->client = new PubSubClient($this->clientConfig);
        }

        return $this->client;
    }

    public function nack(Message $getMessage, Subscription $getSubscription): void
    {
        // everything else than ack will result in nack
    }

    public function getClientConfig(): array
    {
        return $this->clientConfig;
    }
}
