<?php

// Arrey because Array is a reserved keyword.
namespace Agna\Resource\Loader\Middleware\Arrey;

use Agna\Resource\Loader\LoaderInterface;
use Agna\Resource\Loader\Middleware\MiddlewareInterface;
use Agna\Resource\Loader\Resource\ResourceInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * ImportsResolver
 *
 * @todo Refactor!
 * - Shouldn't be a yaml only resolver. Create an array resource type and connect the resolver to that.
 * - File based resolver, could be generic?
 *
 * @package Agna\Resource\Loader\Reader\Reader\File\Yaml
 * @author Agoston Nagy <agoston.nagy@use.github.please>
 */
class ImportsResolver implements MiddlewareInterface
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var LoaderInterface
     */
    protected $loader;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    /**
     * @inheritdoc
     */
    public function __invoke(ResourceInterface $resource, LoaderInterface $loader, callable $next)
    {
        if (!is_array($resource->getData())) {
            throw new \InvalidArgumentException('Invalid resource data, must be an array!');
        }

        $resource = $next($resource);

        $basePath = dirname($resource->getLocation());
        $data = $resource->getData();

        if (isset($data['imports'])) {

            $imports = (array)$data['imports'];
            unset($data['imports']);

            $importsData = [];
            foreach ($imports as $import) {

                if (!$this->filesystem->isAbsolutePath($import)) {
                    $import = $basePath . DIRECTORY_SEPARATOR . $import;
                }

                $importsData = array_replace_recursive($importsData, $loader->load($import)->getData());
            }

            $data = array_replace_recursive($importsData, $data);
        }

        return $resource->cloneWithData($data);
    }
}