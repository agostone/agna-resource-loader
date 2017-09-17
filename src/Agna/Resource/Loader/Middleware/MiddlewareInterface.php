<?php

namespace Agna\Resource\Loader\Middleware;

use Agna\Resource\Loader\LoaderInterface;
use Agna\Resource\Loader\Resource\ResourceInterface;

/**
 * MiddlewareInterface
 *
 * @package Agna\Resource\Middleware
 * @author Agoston Nagy <agoston.nagy@use.github.please>
 */
interface MiddlewareInterface
{
    /**
     * @param ResourceInterface $resource
     * @param LoaderInterface $loader
     * @param callable $next
     * @return ResourceInterface
     */
    public function __invoke(ResourceInterface $resource, LoaderInterface $loader, callable $next);
}