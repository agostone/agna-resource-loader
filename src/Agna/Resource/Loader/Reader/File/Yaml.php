<?php

namespace Agna\Resource\Loader\Reader\File;

use Agna\Resource\Loader\Resource\ResourceInterface;
use Agna\Resource\Loader\Reader\File;
use Symfony\Component\Yaml\Yaml as YamlParser;
use Agna\Resource\Loader\Resource\File\Yaml as YamlResource;

/**
 * YamlLoader
 *
 * @package Agna\Resource\Loader\Reader\File
 * @author Agoston Nagy <agoston.nagy@use.github.please>
 */
class Yaml extends File
{
    /**
     * @inheritdoc
     */
    public function getSupportedExtensions()
    {
        return [
            'yml',
            'yaml'
        ];
    }

    /**
     * @param mixed $source
     * @return YamlResource
     */
    public function createResource($source)
    {
        $this->isValidSource($source);
        return new YamlResource($source);
    }

    /**
     * @inheritdoc
     */
    public function load(ResourceInterface $resource)
    {
        $resource = parent::load($resource);
        return $resource->cloneWithData(YamlParser::parse($resource->getData()));
    }

    /**
     * @inheritdoc
     */
    public function getSupportedType()
    {
        return YamlResource::TYPE_YAML_FILE;
    }
}