<?php
declare(strict_types=1);
namespace CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport;

use CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport\Config\SubscriptionConfig;
use CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport\Config\TopicConfig;
use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\Subscription;
use Google\Cloud\PubSub\Topic;

class Connection
{
    private PubSubClient $client;

    private SubscriptionConfig $subscriptionConfig;

    private TopicConfig $topicConfig;

    private ?Subscription $subscription = null;

    public function __construct(PubSubClient $client, SubscriptionConfig $subscriptionConfig, TopicConfig $topicOptions)
    {
        $this->client             = $client;
        $this->topicConfig        = $topicOptions;
        $this->subscriptionConfig = $subscriptionConfig;
    }

    public function setup(): void
    {
        $topicName = $this->topicConfig->getName();

        if (!$this->client->topic($topicName)->exists()) {
            $this->client->topic($topicName)->create();
        }

        $subscriptionName = $this->subscriptionConfig->getName();

        if (!$this->client->subscription($subscriptionName, $topicName)->exists()) {
            $this->client->subscription($subscriptionName, $topicName)->create();
        }
    }

    public function publish(string $body, array $headers = []): array
    {
        return $this->publishOnTopic(
            $this->topic(),
            $body,
            $headers,
        );
    }

    public function get(): ?Message
    {
        /* TODO: It would be more efficient to consume a batch of messages instead of only one
        Would likely make sense to have the batch size be configurable?
        @see https://github.com/googleapis/google-cloud-php/issues/939#issuecomment-668424240 */
        /* TODO: Streaming pull support would also be great, but that would be more complex to implement
        There's no production ready library/solution for PHP available at this point
        @see https://github.com/googleapis/google-cloud-php/issues/939#issuecomment-713343375 for a POC */
        $messages = $this->getSubscription()->pull(['maxMessages' => 1]);

        return $messages[0] ?? null;
    }

    public function getSubscription(): Subscription
    {
        if (!$this->subscription instanceof Subscription) {
            $this->subscription = $this->client->subscription(
                $this->subscriptionConfig->getName(),
                $this->topicConfig->getName(),
            );
        }

        return $this->subscription;
    }

    public function ack(Message $message, Subscription $subscription): void
    {
        $subscription->acknowledge($message);
    }

    public function nack(Message $getMessage, Subscription $getSubscription): void
    {
        // everything else than ack will result in nack
    }

    public function getClient(): PubSubClient
    {
        return $this->client;
    }

    private function publishOnTopic(Topic $topic, string $body, array $headers): array
    {
        return $topic->publish(new Message([
            'attributes' => $headers,
            'data'       => $body,
        ]));
    }

    private function topic(): Topic
    {
        return $this->client->topic($this->topicConfig->getName());
    }
}
