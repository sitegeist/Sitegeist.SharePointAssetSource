<?php

declare(strict_types=1);

namespace Sitegeist\SharePointAssetSource;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Media\Domain\Model\AssetSource\AssetProxyRepositoryInterface;
use Neos\Media\Domain\Model\AssetSource\AssetSourceInterface;
use Office365\Runtime\Auth\ClientCredential;
use Office365\SharePoint\ClientContext;
use Sitegeist\SharePointAssetSource\Infrastructure\SharePointAssetProxyRepository;

#[Flow\Proxy(false)]
final class SharePointAssetSource implements AssetSourceInterface
{
    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    public function __construct(
        public readonly string $identifier,
        public readonly string $label,
        private readonly string $iconPath,
        public readonly string $description,
        public readonly bool $isReadOnly,
        private readonly ClientContext $client,
        public readonly string $rootFolderPath,
    ) {
    }

    /**
     * @param array<string,mixed> $assetSourceOptions
     */
    public static function createFromConfiguration(
        string $assetSourceIdentifier,
        array $assetSourceOptions
    ): AssetSourceInterface {
        if (preg_match('/^[a-z][a-z0-9-]{0,62}[a-z]$/', $assetSourceIdentifier) !== 1) {
            throw new \InvalidArgumentException(sprintf('Invalid asset source identifier "%s". The identifier must match /^[a-z][a-z0-9-]{0,62}[a-z]$/', $assetSourceIdentifier), 1513329665);
        }

        $label = $assetSourceOptions['label'] ?? 'SharePoint';
        if (!is_string($label)) {
            throw new \InvalidArgumentException('Asset source labels must be of type string', 1739534568);
        }

        $iconPath = $assetSourceOptions['icon'] ?? 'resource://Neos.Media/Public/Icons/NeosWhite.svg';
        if (!is_string($iconPath) || !\str_starts_with($iconPath, 'resource://')) {
            throw new \InvalidArgumentException('Icons must be resource path strings', 1739534952);
        }

        $description = $assetSourceOptions['description'] ?? '';
        if (!is_string($description)) {
            throw new \InvalidArgumentException('Asset source descriptions must be of type string', 1739535070);
        }

        $isReadOnly = array_key_exists('readonly', $assetSourceOptions)
            ? (bool)$assetSourceOptions['readonly']
            : false;

        $clientContextConfig = $assetSourceOptions['clientContext'] ?? null;
        if (
            !is_array($clientContextConfig)
            || !is_string($siteUrl = $clientContextConfig['siteUrl'] ?? null)
            || !is_string($clientId = $clientContextConfig['clientCredentials']['clientId'] ?? null)
            || !is_string($clientSecret = $clientContextConfig['clientCredentials']['clientSecret'] ?? null)
        ) {
            throw new \InvalidArgumentException('The client context must be configured as array{siteUrl: string, clientCredentials: array{clientId: string, clientSecret: string}}', 1739540049);
        }

        $rootFolderPath = $assetSourceOptions['rootFolderPath'] ?? '';
        if (!is_string($rootFolderPath)) {
            throw new \InvalidArgumentException('Root folder paths must be of type string', 1739551909);
        }

        $client = (new ClientContext($siteUrl))
            ->withCredentials(new ClientCredential($clientId, $clientSecret));

        return new self(
            identifier: $assetSourceIdentifier,
            label: $label,
            iconPath: $assetSourceOptions['icon'] ?? '',
            description: $description,
            isReadOnly: $isReadOnly,
            client: $client,
            rootFolderPath: $rootFolderPath,
        );
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getIconUri(): string
    {
        return $this->resourceManager?->getPublicPackageResourceUriByPath($this->iconPath) ?: '';
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getAssetProxyRepository(): AssetProxyRepositoryInterface
    {
        return new SharepointAssetProxyRepository($this->client, $this);
    }

    public function isReadOnly(): bool
    {
        return $this->isReadOnly;
    }
}
