<?php

namespace Agna\Resource\Loader\Resource;

/**
 * Interface ResourceInterface
 *
 * @package Agna\Resource\Loader
 */
interface ResourceInterface
{
    /**
     * @return string|array
     */
    public function getType();

    /**
     * @param string $targetType
     * @return boolean
     */
    public function isTypeOf($targetType);

    /**
     * @return mixed
     */
    public function getData();

    /**
     * @param mixed $data
     * @return ResourceInterface
     */
    public function cloneWithData($data);

    /**
     * @return string
     */
    public function __toString();
}