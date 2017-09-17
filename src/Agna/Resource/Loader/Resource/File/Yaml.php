<?php

namespace Agna\Resource\Loader\Resource\File;

use Agna\Resource\Loader\Resource\File;

/**
 * Resource
 *
 * @package Agna\Resource\Loader\Reader\File\Yaml
 * @author Agoston Nagy <agoston.nagy@use.github.please>
 */
class Yaml extends File
{
    const TYPE_YAML_FILE = 'yamlFileResource';

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return [
            parent::getType(),
            static::TYPE_YAML_FILE
        ];
    }
}