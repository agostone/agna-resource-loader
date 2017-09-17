<?php

namespace Agna\Resource\Loader\Reader;

use Agna\Resource\Loader\Resource\ResourceInterface;

/**
 * ReaderInterface
 *
 * @package HCSS\File\Loader\Reader
 * @author Agoston Nagy <agoston.nagy@use.github.please>
 */
interface ReaderInterface
{
    /**
     * @param mixed $location
     *
     * @throws InvalidLocationException
     *
     * @return true
     */
    public function isValidLocation($location);

    /**
     * @param mixed $location
     *
     * @throws InvalidLocationException
     *
     * @return ResourceInterface[]
     */
    public function listResources($location);

    /**
     * @param mixed $source
     *
     * @throws InvalidResourceException
     *
     * @return true
     */
    public function isValidSource($source);

    /**
     * @param ResourceInterface $resource
     *
     * @throws InvalidResourceException
     *
     * @return true
     */
    public function isValidResource(ResourceInterface $resource);

    /**
     * @param ResourceInterface $resource
     *
     * @throws InvalidResourceException
     *
     * @return ResourceInterface
     */
    public function load(ResourceInterface $resource);

    /**
     * @param mixed $source
     * @return ResourceInterface
     */
    public function createResource($source);

    /**
     * @return string|array
     */
    public function getSupportedType();

    /**
     * @return string
     */
    public static function getName();
}