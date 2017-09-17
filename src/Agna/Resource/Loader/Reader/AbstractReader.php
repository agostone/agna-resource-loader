<?php

namespace Agna\Resource\Loader\Reader;

/**
 * AbstractReader
 *
 * @package Agna\Resource\Loader\Reader
 * @author Agoston Nagy <agoston.nagy@use.github.please>
 */
abstract class AbstractReader implements ReaderInterface
{
    /**
     * @inheritdoc
     */
    public static function getName()
    {
        return static::class;
    }
}