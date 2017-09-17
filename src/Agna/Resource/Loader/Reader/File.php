<?php

namespace Agna\Resource\Loader\Reader;

use Agna\Resource\Loader\Resource\ResourceInterface;
use Agna\Resource\Loader\Resource\File as FileResource;

/**
 * File
 *
 * @package Agna\Resource\Loader\Reader\File
 * @author Agoston Nagy <agoston.nagy@use.github.please>
 */
class File extends AbstractReader
{
    private static $allExtensions = '*';

    /**
     * @return array|string
     */
    public function getSupportedExtensions()
    {
        return static::$allExtensions;
    }

    /**
     * @inheritdoc
     */
    public function isValidLocation($location)
    {
        if (!is_dir($location) || !is_readable($location)) {
            throw new InvalidLocationException('$location should point to a valid and readable directory!');
        }

        return true;
    }

    /**
     * @param string $location
     *
     * @throws InvalidLocationException
     *
     * @return FileResource[]
     */
    public function listResources($location)
    {
        $this->isValidLocation($location);

        $supportedExtensions = $this->getSupportedExtensions();

        $pattern = $location . DIRECTORY_SEPARATOR . '*.' . (
            is_array($supportedExtensions)
                ? '{' . implode(',', $supportedExtensions) . '}'
                : $supportedExtensions
        );

        return array_map(function ($entry) {
            return $this->createResource($entry);
        }, glob($pattern, GLOB_BRACE));
    }

    /**
     * @inheritdoc
     */
    public function isValidSource($source)
    {
        if (!is_string($source)) {
            throw new InvalidResourceException('$source argument must be a string pointing to a valid and readable file!');
        }

        $source = realpath($source);
        if (!is_file($source) && !is_readable($source)) {
            throw new InvalidResourceException('$source argument should point to a valid and readable file!');
        }

        $supportedExtensions = $this->getSupportedExtensions();

        if (self::$allExtensions !== $supportedExtensions) {
            $extension = pathinfo($source, PATHINFO_EXTENSION);
            if (!in_array($extension, $supportedExtensions)) {
                throw new InvalidResourceException(sprintf('\'%s\' resource is not supported by the loader!',
                    $source));
            }
        }

        return true;
    }

    /**
     * @param FileResource $resource
     *
     * @throws InvalidResourceException
     *
     * @return FileResource
     */
    public function load(ResourceInterface $resource)
    {
        $this->isValidResource($resource);

        $data = file_get_contents($resource->getLocation());

        return $resource->cloneWithData($data);
    }

    /**
     * @param mixed $source
     * @return FileResource
     */
    public function createResource($source)
    {
        $this->isValidSource($source);
        return new FileResource($source);
    }

    /**
     * @inheritdoc
     */
    public function isValidResource(ResourceInterface $resource)
    {
        $supportedType = (array) $this->getSupportedType();
        $resourceType = (array) $resource->getType();

        if (empty(array_intersect($supportedType, $resourceType))) {
            throw new InvalidResourceException('$resource is not supported by this reader!');
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getSupportedType()
    {
        return FileResource::TYPE_FILE;
    }
}