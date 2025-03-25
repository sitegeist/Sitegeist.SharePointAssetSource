<?php

declare(strict_types=1);

namespace Sitegeist\SharePointAssetSource\Infrastructure;

use Neos\Flow\Annotations as Flow;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\AssetProxyInterface;
use Neos\Media\Domain\Model\AssetSource\AssetSourceInterface;
use Office365\SharePoint\File;
use Psr\Http\Message\UriInterface;
use Sitegeist\SharePointAssetSource\SharePointAssetSource;

#[Flow\Proxy(false)]
final class SharePointAssetProxy implements AssetProxyInterface
{
    public function __construct(
        public readonly File $file,
        private readonly SharePointAssetSource $assetSource,
    ) {
    }

    public function getAssetSource(): AssetSourceInterface
    {
        return $this->assetSource;
    }

    public function getIdentifier(): string
    {
        return $this->file->getUniqueId() ?: '';
    }

    public function getLabel(): string
    {
        return $this->file->getTitle() ?: $this->file->getName() ?: '';
    }

    public function getFilename(): string
    {
        return $this->file->getName() ?: '';
    }

    public function getLastModified(): \DateTimeInterface
    {
        return new \DateTimeImmutable($this->file->getTimeLastModified() ?: 'now');
    }

    public function getFileSize(): int
    {
        return (int)$this->file->getLength();
    }

    public function getMediaType(): string
    {
        return '';
    }

    public function getWidthInPixels(): ?int
    {
        return null;
    }

    public function getHeightInPixels(): ?int
    {
        return null;
    }

    public function getThumbnailUri(): ?UriInterface
    {
        return null;
    }

    public function getPreviewUri(): ?UriInterface
    {
        return null;
    }

    public function getImportStream()
    {
        throw new \BadMethodCallException('Not implemented');
    }

    public function getLocalAssetIdentifier(): ?string
    {
        return null;
    }
}
