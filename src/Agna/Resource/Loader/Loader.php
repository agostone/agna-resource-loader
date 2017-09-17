<?php

namespace Agna\Resource\Loader;

use Agna\Resource\Loader\Reader\InvalidLocationException;
use Agna\Resource\Loader\Resource\ResourceInterface;
use Agna\Resource\Loader\Reader\InvalidResourceException;
use Agna\Resource\Loader\Reader\ReaderInterface;
use Zend\Stdlib\SplStack;

/**
 * Resource loader
 *
 * @todo Refactor!
 * - Rename to LoaderAggregate?
 * - Loader should have one reader?
 *
 * @author Agoston Nagy <agoston.nagy@use.github.please>
 */
class Loader implements LoaderInterface
{
    /**
     * @var ReaderInterface[]
     */
    protected $reader = [];
    protected $middlewares = [];

    protected $executions = [];

    /**
     * ResourceLoader constructor.
     *
     * @param ReaderInterface|ReaderInterface[]|null $reader
     */
    public function __construct($reader = null)
    {
        if ($reader) {
            $this->addReader($reader);
        }
    }

    /**
     * @inheritdoc
     */
    public function load($source)
    {
        $reader = null;

        foreach ($this->reader as $reader) {
            try {
                $reader->isValidSource($source);
                break;
            } catch (InvalidResourceException $invalidResourceException) {
                $reader = null;
            }
        }

        if (null === $reader) {
            throw new InvalidResourceException('No registered reader can handle the $source!');
        }

        $start = $this->middlewares[$reader->getName()]->top();
        return $start($reader->createResource($source));
    }

    /**
     * @inheritdoc
     */
    public function listResources($location)
    {
        foreach ($this->reader as $reader) {

            try {
                $reader->isValidLocation($location);
                break;
            } catch (InvalidLocationException $invalidLocationException) {
                $reader = null;
            }
        }

        return $reader ? $reader->listResources($location) : [];
    }

    /**
     * @inheritdoc
     */
    public function addReader($reader)
    {
        $reader = $reader instanceof ReaderInterface ? [$reader] : $reader;

        foreach ($reader as $newReader) {

            $readerName = $newReader::getName();

            if (isset($this->reader[$readerName])) {
                break;
            }

            $this->reader[$readerName] = $newReader;
            $this->initializeMiddlewareStack($readerName);
        }

        return $this->reader;
    }

    /**
     * @inheritdoc
     */
    public function removeReader($reader)
    {
        $reader = $reader instanceof ReaderInterface ? [$reader] : $reader;

        foreach ($reader as $newReader) {

            $readerName = $newReader::getName();

            if (!isset($this->reader[$readerName])) {
                break;
            }

            unset($this->reader[$readerName], $this->middlewares[$readerName]);
        }

        return $this->reader;
    }

    /**
     * @inheritdoc
     */
    public function getReader($name = null)
    {
        return $name
            ? isset($this->reader[$name])
                ? $this->reader[$name] : null
            : $this->reader;
    }

    /**
     * @param string $readerName
     * @param callable $callable
     * @return $this
     */
    public function addMiddleware($readerName, callable $callable)
    {
        if (!is_string($readerName) || !isset($this->middlewares[$readerName])) {
            throw new \InvalidArgumentException('$readerName should be string and contain a valid reader name!');
        }

        $stack = $this->middlewares[$readerName];

        $next = $stack->top();
        $stack[] = function (
            ResourceInterface $resource
        ) use (
            $callable,
            $next
        ) {
            $result = $callable($resource, $this, $next);
            if (false === $result instanceof ResourceInterface) {
                throw new \UnexpectedValueException(
                    sprintf('Middleware must return instance of %s!', ResourceInterface::class)
                );
            }

            return $result;
        };

        return $this;
    }

    /**
     * @param $readerName
     *
     * @throws \RuntimeException if the stack is seeded more than once
     */
    protected function initializeMiddlewareStack($readerName)
    {
        if (isset($this->middlewares[$readerName])) {
            throw new \RuntimeException('MiddlewareStack can only be initialized once.');
        }

        $stack = $this->middlewares[$readerName] = new SplStack();
        $stack->setIteratorMode(\SplDoublyLinkedList::IT_MODE_LIFO | \SplDoublyLinkedList::IT_MODE_KEEP);

        $stack[] = function (ResourceInterface $resource) use ($readerName) {
            return $this->reader[$readerName]->load($resource);
        };
    }

    /**
     * @inheritdoc
     */
    public function hasMiddlewares($readerName)
    {
        return isset($this->middlewares[$readerName]) ? !$this->middlewares[$readerName]->isEmpty() : false;
    }

}
