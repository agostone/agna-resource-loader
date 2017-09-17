<?php

namespace Agna\Resource\Loader;

use Agna\Resource\Loader\Resource\ResourceInterface;
use Agna\Resource\Loader\Reader\ReaderInterface;

interface LoaderInterface
{
    const EVENT_PRE_LOAD = 'preResourceLoad';
    const EVENT_POST_LOAD = 'postResourceLoad';

    /**
     * Returns with the transformed file content of a desired file.
     *
     * @param mixed $source Resource location
     * @return ResourceInterface
     */
    public function load($source);

    /**
     * Retruns with all supported resources in a given location.
     *
     * @param mixed $location
     * @return array
     */
    public function listResources($location);

    /**
     * Adds a resource reader.
     *
     * @param ReaderInterface|ReaderInterface[] $reader
     * @return array
     */
    public function addReader($reader);

    /**
     * Removes a resource reader.
     *
     * @param ReaderInterface|ReaderInterface[] $reader
     * @return array
     */
    public function removeReader($reader);

    /**
     * Adds a reader middleware
     *
     * @param string $readerName
     * @param callable $callable
     * @return LoaderInterface
     */
    public function addMiddleware($readerName, callable $callable);

    /**
     * Returns with a resource reader
     *
     *
     * @param string|null $name
     * @return ReaderInterface|ReaderInterface[]
     */
    public function getReader($name = null);

    /**
     * Checks is a specific reader has middlewares or not.
     *
     * @param string $readerName
     * @return boolean
     */
    public function hasMiddlewares($readerName);
}
