<?php
declare(strict_types=1);

namespace CedricZiel\Symfony\Messenger\Bridge\GcpPubSub\Transport\Config;

class ClientConfig
{
    private string $projectId;

    private ?string $apiEndpoint;

    private ?string $keyFilePath;

    private ?string $requestTimeout;

    private ?string $retries;

    private ?string $scopes;

    private ?string $quotaProject;

    private ?string $transport;

    public static function fromArray(array $array): self
    {
        $projectId      = null;
        $apiEndpoint    = null;
        $keyFilePath    = null;
        $requestTimeout = null;
        $retries        = null;
        $scopes         = null;
        $quotaProject   = null;
        $transport      = null;

        foreach ($array as $key => $item) {
            if ($key === 'projectId') {
                $projectId = $item;
            }
            if ($key === 'apiEndpoint') {
                $apiEndpoint = $item;
            }
            if ($key === 'keyFilePath') {
                $keyFilePath = $item;
            }
            if ($key === 'requestTimeout') {
                $requestTimeout = $item;
            }
            if ($key === 'retries') {
                $retries = $item;
            }
            if ($key === 'scopes') {
                $scopes = $item;
            }
            if ($key === 'quotaProject') {
                $quotaProject = $item;
            }
            if ($key === 'transport') {
                $transport = $item;
            }
        }

        return new self(
            $projectId,
            $apiEndpoint,
            $keyFilePath,
            $requestTimeout,
            $retries,
            $scopes,
            $quotaProject,
            $transport
        );
    }

    private function __construct(
        string $projectId,
        ?string $apiEndpoint = null,
        ?string $keyFilePath = null,
        ?string $requestTimeout = null,
        ?string $retries = null,
        ?string $scopes = null,
        ?string $quotaProject = null,
        ?string $transport = null
    )
    {
        $this->projectId      = $projectId;
        $this->apiEndpoint    = $apiEndpoint;
        $this->keyFilePath    = $keyFilePath;
        $this->requestTimeout = $requestTimeout;
        $this->retries        = $retries;
        $this->scopes         = $scopes;
        $this->quotaProject   = $quotaProject;
        $this->transport      = $transport;
    }

    public function toArray(): array
    {
        $array = ['projectId'   => $this->projectId];

        if ($this->apiEndpoint) {
            $array['apiEndpoint'] = $this->apiEndpoint;
        }
        if ($this->keyFilePath) {
            $array['keyFilePath'] = $this->keyFilePath;
        }
        if ($this->requestTimeout) {
            $array['requestTimeout'] = $this->requestTimeout;
        }
        if ($this->retries) {
            $array['retries'] = $this->retries;
        }
        if ($this->scopes) {
            $array['scopes'] = $this->scopes;
        }
        if ($this->quotaProject) {
            $array['quotaProject'] = $this->quotaProject;
        }
        if ($this->transport) {
            $array['transport'] = $this->transport;
        }

        return $array;
    }
}
