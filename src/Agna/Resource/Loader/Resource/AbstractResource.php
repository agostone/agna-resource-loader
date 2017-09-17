<?php

namespace Agna\Resource\Loader\Resource;

/**
 * AbstractResource
 *
 * @package Agna\Resource\Loader
 * @author Agoston Nagy <agoston.nagy@use.github.please>
 */
abstract class AbstractResource implements ResourceInterface
{
    /**
     * @var mixed
     */
    protected $data;

    /**
     * AbstractResource constructor.
     *
     * @param string $type
     * @param mixed $location
     * @param mixed $data (default: null)
     */
    public function __construct($data = null)
    {
        $this->setData($data);
    }

    /**
     * @param string $type
     * @return boolean
     */
    public function isTypeOf($targetType)
    {
        $type = $this->getType();

        return
            (is_string($type) && $type == $targetType) || (is_array($type) && in_array($targetType, $type));
    }

    /**
     * @param mixed $data
     * @return AbstractResource
     */
    protected function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     * @return AbstractResource
     */
    public function cloneWithData($data)
    {
        $clone = clone $this;
        return $clone->setData($data);
    }
}