<?php

declare(strict_types=1);

namespace Sitegeist\SharePointAssetSource\Infrastructure;

use Neos\Media\Domain\Model\AssetSource\AssetProxy\AssetProxyInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxyQueryResultInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxyRepositoryInterface;
use Neos\Media\Domain\Model\AssetSource\AssetTypeFilter;
use Neos\Media\Domain\Model\Tag;
use Office365\SharePoint\ClientContext;
use Sitegeist\SharePointAssetSource\SharePointAssetSource;

final class SharePointAssetProxyRepository implements AssetProxyRepositoryInterface
{
    public function __construct(
        protected readonly ClientContext $client,
        protected readonly SharePointAssetSource $assetSource,
    ) {
    }

    public function getAssetProxy(string $identifier): AssetProxyInterface
    {
        $client = clone $this->client;
        $file = $client->getWeb()->getFileById($identifier);
        $client->load($file);
        $client->executeQuery();

        return new SharePointAssetProxy($file, $this->assetSource);
    }

    public function filterByType(?AssetTypeFilter $assetType = null): void
    {
        // TODO: Implement filterByType() method.
    }

    public function findAll(): AssetProxyQueryResultInterface
    {
        $client = clone $this->client;

        $query = new SharePointAssetProxyQuery(
            $client,
            $this->assetSource
        );

        return $query->execute();
    }

    public function findBySearchTerm(string $searchTerm): AssetProxyQueryResultInterface
    {
        // TODO: Implement findBySearchTerm() method.
    }

    public function findByTag(Tag $tag): AssetProxyQueryResultInterface
    {
        // TODO: Implement findByTag() method.
    }

    public function findUntagged(): AssetProxyQueryResultInterface
    {
        // TODO: Implement findUntagged() method.
    }

    public function countAll(): int
    {
        return 0;
    }
}
