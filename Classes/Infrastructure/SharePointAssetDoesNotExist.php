<?php

declare(strict_types=1);

namespace Sitegeist\SharePointAssetSource\Infrastructure;

use Neos\Media\Domain\Model\AssetSource\AssetNotFoundExceptionInterface;
use Neos\Media\Exception;

final class SharePointAssetDoesNotExist extends Exception implements AssetNotFoundExceptionInterface
{
    /**
     * @var int
     */
    protected $statusCode = 404;
}
