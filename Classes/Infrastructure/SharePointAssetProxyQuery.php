<?php

declare(strict_types=1);

namespace Sitegeist\SharePointAssetSource\Infrastructure;

use Neos\Media\Domain\Model\AssetSource\AssetProxyQueryInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxyQueryResultInterface;
use Neos\Flow\Annotations as Flow;
use Office365\SharePoint\ClientContext;
use Office365\SharePoint\FileCollection;
use Office365\SharePoint\Folder;
use Sitegeist\SharePointAssetSource\SharePointAssetSource;

#[Flow\Proxy(false)]
final class SharePointAssetProxyQuery implements AssetProxyQueryInterface
{
    public function __construct(
        private readonly ClientContext $client,
        private readonly SharePointAssetSource $assetSource,
        private ?string $searchTerm = null,
        private int $limit = 90000,
        private int $offset = 0,
    ) {
    }

    public function setOffset(int $offset): void
    {
        $this->offset = $offset;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setSearchTerm(string $searchTerm): void
    {
        $this->searchTerm = $searchTerm;
    }

    public function getSearchTerm(): ?string
    {
        return $this->searchTerm;
    }

    public function execute(): AssetProxyQueryResultInterface
    {
        $rootFolder = $this->client->getWeb()
            ->getFolderByServerRelativeUrl($this->assetSource->rootFolderPath);

        return new SharePointAssetProxyQueryResult($this->loadFolderRecursively($rootFolder), $this->assetSource, $this);
    }

    /**
     * @return array<FileCollection> one file collection per folder
     */
    private function loadFolderRecursively(Folder $folder, int $numberOfItemsSoFar = 0): array
    {
        $files = $folder->getFiles();
        $this->client->load($files);
        $this->client->executeQuery();

        $numberOfItemsSoFar += $files->getCount();

        $result = [$files];

        if ($numberOfItemsSoFar < $this->limit) {
            $childFolders = $folder->getFolders();
            $this->client->load($childFolders);
            $this->client->executeQuery();
            foreach ($childFolders as $childFolder) {
                $result = array_merge($result, $this->loadFolderRecursively($childFolder, $numberOfItemsSoFar));
            }
        }

        return $result;
    }

    public function count(): int
    {
        return $this->execute()->count();
    }
}
