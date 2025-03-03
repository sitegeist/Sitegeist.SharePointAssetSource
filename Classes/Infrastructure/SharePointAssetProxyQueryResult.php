<?php

declare(strict_types=1);

namespace Sitegeist\SharePointAssetSource\Infrastructure;

use Neos\Media\Domain\Model\AssetSource\AssetProxy\AssetProxyInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxyQueryInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxyQueryResultInterface;
use Neos\Flow\Annotations as Flow;
use Office365\SharePoint\File;
use Office365\SharePoint\FileCollection;
use Sitegeist\SharePointAssetSource\SharePointAssetSource;

#[Flow\Proxy(false)]
final class SharePointAssetProxyQueryResult implements AssetProxyQueryResultInterface
{
    /**
     * @var array<File> $files
     */
    private array $files;

    /**
     * @param array<FileCollection> $filesPerFolder
     */
    public function __construct(
        array $filesPerFolder,
        private readonly SharePointAssetSource $assetSource,
        private readonly SharePointAssetProxyQuery $query,
    ) {
        $files = [];
        foreach ($filesPerFolder as $fileCollection) {
            $files = array_merge($files, iterator_to_array($fileCollection));
        }

        $this->files = $files;
    }

    public function getQuery(): AssetProxyQueryInterface
    {
        return $this->query;
    }

    public function getFirst(): ?AssetProxyInterface
    {
        $first = reset($this->files);

        return $first instanceof File
            ? new SharePointAssetProxy($first, $this->assetSource)
            : null;
    }

    /**
     * @return array|AssetProxyInterface[]
     */
    public function toArray(): array
    {
        return array_map(
            fn (File $file): SharePointAssetProxy => new SharePointAssetProxy($file, $this->assetSource),
            $this->files,
        );
    }

    public function current(): mixed
    {
        $current = current($this->files);

        return $current instanceof File
            ? new SharePointAssetProxy($current, $this->assetSource)
            : null;
    }

    public function next(): void
    {
        next($this->files);
    }

    public function key(): null|int|string
    {
        return key($this->files);
    }

    public function valid(): bool
    {
        return $this->current() instanceof SharePointAssetProxy;
    }

    public function rewind(): void
    {
        reset($this->files);
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->files);
    }

    /**
     * @param mixed $offset
     */
    public function offsetGet($offset): ?AssetProxyInterface
    {
        $file = $this->files[$offset] ?? null;
        return $file instanceof File
            ? new SharePointAssetProxy($file, $this->assetSource)
            : null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): never
    {
        throw new \BadMethodCallException('Unsupported operation: ' . __METHOD__, 1510060444);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset): never
    {
        throw new \BadMethodCallException('Unsupported operation: ' . __METHOD__, 1510060467);
    }

    public function count(): int
    {
        return count($this->files);
    }
}
