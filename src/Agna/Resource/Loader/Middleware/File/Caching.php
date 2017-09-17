<?php

namespace Agna\Resource\Loader\Middleware\File;

use Agna\Resource\Loader\LoaderInterface;
use Agna\Resource\Loader\Resource\File;
use Agna\Resource\Loader\Resource\ResourceInterface;
use Agna\Resource\Loader\Middleware\MiddlewareInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Caching
 *
 * @todo Refactor!
 * - Replace static file based caching with a cache provider (loadCache, getCache, getCacheMeta, etc).
 * - Injectable serializer/unserializer.
 * - Should i turn this into a generic caching middleware or not? Inject shouldRefresh? Any point in doing that?
 *
 * @package Agna\Resource\Loader\Reader\File
 * @author Agoston Nagy <agoston.nagy@use.github.please>
 */
class Caching implements MiddlewareInterface
{
    protected $autoRefresh;
    protected $files = [];
    protected $cachePath;
    protected $filesystem;
    protected $permission;

    protected $metaPostFix = '.meta';

    /**
     * @var bool Flag to avoid endless recursion
     */
    private $collecting;

    /**
     * Caching constructor.
     *
     * @param string $cachePath
     * @param bool $autoRefresh (default: false)
     * @param int $permission (default: 0664)
     */
    public function __construct($cachePath, $autoRefresh = false, $permission = 0664)
    {
        $cachePath = rtrim(realpath($cachePath), '/\\');

        if (!is_string($cachePath) || !is_dir($cachePath) || !is_writable($cachePath)) {
            throw new \InvalidArgumentException('$cachePath should point to a valid and writable directory!');
        }

        $this->cachePath = $cachePath;
        $this->autoRefresh = $autoRefresh;
        $this->collecting = false;
        $this->permission = $permission;

        $this->filesystem = new Filesystem();
    }

    /**
     * @param string $postFix
     * @return Caching
     */
    public function setMetaPostfix($postFix)
    {
        $this->metaPostFix = $postFix;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function __invoke(ResourceInterface $resource, LoaderInterface $loader, callable $next)
    {
        /** @var $resource File */
        if (!$resource->isTypeOf(File::TYPE_FILE)) {
            throw new \InvalidArgumentException(sprintf('Invalid resource type, can only handle \'%s\' resource type!', File::TYPE_FILE));
        }

        if (false === $this->collecting) {

            $cache = $this->loadCache($resource);

            // Cache hit, returning with cached data
            if (false === $this->shouldRefresh($resource) && false !== $cache) {
                return $resource->cloneWithData($cache);
            }

            $this->collecting = (string) $resource;
        }

        // Storing meta information
        if ($this->autoRefresh) {
            $this->files[(string) $resource] = filemtime($resource);
        }

        $resource = $next($resource);

        if ($this->collecting === (string) $resource) {

            $this->filesystem->dumpFile(
                $this->getCacheFile((string) $resource),
                $this->serialize($resource->getData())
            );
            $this->filesystem->chmod($this->getCacheFile((string) $resource), $this->permission);
            $this->collecting = false;

            if ($this->autoRefresh) {
                $this->filesystem->dumpFile(
                    $this->getCacheMetaFile((string) $resource),
                    $this->serialize($this->files)
                );
                $this->filesystem->chmod($this->getCacheMetaFile((string) $resource), $this->permission);
                $this->files = [];
            }
        }

        return $resource;
    }

    /**
     * @param string $resourceId
     * @return bool|mixed
     */
    protected function loadCache($resourceId)
    {
        $cacheFile = $this->getCacheFile($resourceId);
        if ($this->filesystem->exists($cacheFile) && is_readable($cacheFile)) {
            return $this->unserialize(file_get_contents($cacheFile));
        }

        return false;
    }

    /**
     * @param string $resourceId
     * @return bool
     */
    protected function shouldRefresh($resourceId)
    {
        if (false === $this->autoRefresh) {
            return false;
        }

        $metaFile = $this->getCacheMetaFile($resourceId);

        if ($this->filesystem->exists($metaFile) || is_readable($metaFile)) {
            $metaData = $this->unserialize(file_get_contents($metaFile));

            foreach ($metaData as $file => $filemtime) {
                if (!is_file($file) || !is_readable($file) || filemtime($file) > $filemtime) {
                    return true;
                }
            }

            return false;
        }

        return true;

    }

    /**
     * @param string $resourceId
     * @return string
     */
    protected function getCacheFile($resourceId)
    {
        return $this->cachePath . DIRECTORY_SEPARATOR . sha1($resourceId);
    }

    /**
     * @param string $resourceId
     * @return string
     */
    protected function getCacheMetaFile($resourceId)
    {
        return $this->getCacheFile($resourceId) . $this->metaPostFix;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    protected function unserialize($value)
    {
        return unserialize($value);
    }

    /**
     * @param mixed $value
     * @return string
     */
    protected function serialize($value)
    {
        return serialize($value);
    }
}