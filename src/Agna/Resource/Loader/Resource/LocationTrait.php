<?php

namespace Agna\Resource\Loader\Resource;

/**
 * LocationTrait
 *
 * @author Agoston Nagy <agoston.nagy@use.github.please>
 */
trait LocationTrait
{
    /**
     * @var mixed
     */
    protected $location;

    /**
     * @return mixed
     */
    public function getLocation()
    {
        return $this->location;
    }
}