<?php

namespace Agna\Resource\Loader;

use Agna\Resource\Loader\Middleware\MiddlewareInterface;
use Agna\Resource\Loader\Reader\ReaderInterface;

/**
 * LoaderBuilder
 *
 * @package Agna\Resource\Loader
 * @author Agoston Nagy <agoston.nagy@use.github.please>
 */
class LoaderBuilder
{
    public function build(array $settings)
    {
        if (!isset($settings['className'])) {
            $settings['className'] = Loader::class;
        }

        if (!isset($settings['reader'])) {
            throw new \InvalidArgumentException('Reader settings are mandatory and missing!');
        }

        // Adding strategies
        $reader = (array) $settings['reader'];

        foreach ($reader as $className => $arguments) {
            if (false === $arguments instanceof ReaderInterface) {
                $reader[$className] = $this->buildReader(
                    $className,
                    !empty($arguments) ? $arguments : []
                );
            }
        }

        // Creating loader
        $loader = $this->buildLoader($settings['className'], $reader);

        if (isset($settings['middlewares']) && is_array($settings['middlewares'])) {

            foreach ($settings['middlewares'] as $readerName => $middlewares) {

                if (!is_string($readerName)) {
                    throw new \InvalidArgumentException('Invalid reader name in middleware information, should be string!');
                }

                foreach ($middlewares as $className => $arguments) {

                    $middleware = is_callable($arguments)
                        ? $arguments
                        : $this->buildMiddleware(
                            $className,
                            !empty($arguments) ? $arguments: []
                        );

                    $loader->addMiddleware($readerName, $middleware);
                }
            }
        }

        return $loader;
    }

    /**
     * @param string $className
     * @param ReaderInterface|ReaderInterface[]|null $reader
     *
     * @return LoaderInterface
     */
    protected function buildLoader($className, $reader = null)
    {
        $loader = new $className($reader);

        if (!$loader instanceof LoaderInterface) {
            throw new \InvalidArgumentException(
                sprintf('$className should reference to a class implementing \'%s\'!', LoaderInterface::class)
            );
        }

        return $loader;
    }

    /**
     * @param string $className
     * @param array $arguments
     * @return mixed
     */
    protected function buildReader($className, $arguments)
    {
        $subscriberReflection = new \ReflectionClass($className);

        if (!$subscriberReflection->implementsInterface(ReaderInterface::class)) {
            throw new \InvalidArgumentException(
                sprintf('$className should reference to a class implementing \'%s\'!', ReaderInterface::class)
            );
        }

        return empty($arguments)
            ? new $className()
            : $subscriberReflection->newInstanceArgs($arguments);
    }

    /**
     * @param string $className
     * @param array $arguments
     *
     * @return mixed
     */
    protected function buildMiddleware($className, $arguments)
    {
        $subscriberReflection = new \ReflectionClass($className);

        if (!$subscriberReflection->implementsInterface(MiddlewareInterface::class)) {
            throw new \InvalidArgumentException(
                sprintf('$className should reference to a class implementing \'%s\'!', MiddlewareInterface::class)
            );
        }

        return empty($arguments)
            ? new $className()
            : $subscriberReflection->newInstanceArgs($arguments);
    }
}