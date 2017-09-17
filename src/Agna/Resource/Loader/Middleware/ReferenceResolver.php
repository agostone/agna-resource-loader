<?php

namespace Agna\Resource\Loader\Middleware;

use Agna\Resource\Loader\LoaderInterface;
use Agna\Resource\Loader\Resource\ResourceInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * ReferenceResolver
 *
 * @package Agna\Resource\Loader\Middleware
 * @author Agoston Nagy <agoston.nagy@dont.mail.me>
 */
class ReferenceResolver implements MiddlewareInterface
{
    /**
     * @var mixed
     */
    protected $container;

    /**
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

    /**
     * @var string
     */
    protected $constantMarker = '%';

    /**
     * @var string
     */
    protected $referenceMaker = '@';

    /**
     * ReferenceResolver constructor.
     *
     * @param $container
     * @param PropertyAccessor|null $propertyAccessor
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($container, PropertyAccessor $propertyAccessor = null)
    {
        if (!is_array($container) && !is_object($container)) {
            throw new \InvalidArgumentException('Invalid $container value, should be an array or an object!');
        }

        $this->container = $container;
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param string $contantMarker
     *
     * @return ReferenceResolver
     */
    public function setConstantMarker($contantMarker)
    {
        $this->constantMarker = $contantMarker;
        return $this;
    }

    /**
     * @param string $referenceMaker
     *
     * @return ReferenceResolver
     */
    public function setReferenceMarker($referenceMaker)
    {
        $this->referenceMaker = $referenceMaker;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function __invoke(ResourceInterface $resource, LoaderInterface $loader, callable $next)
    {
        $resource = $next($resource);

        $data = $this->resolveReferences($resource->getData());

        return $resource->cloneWithData($data);
    }

    /**
     * @param mixed $data
     *
     * @return array|\ArrayAccess|\Iterator
     */
    protected function resolveReferences($data)
    {
        // Most probably an object
        if (!is_array($data) && !$data instanceof \ArrayAccess && !$data instanceof \Iterator) {

            $reflection = new \ReflectionClass($data);
            $properties = $reflection->getProperties();

            foreach ($properties as $property) {

                $key = $property->getName();
                if ($this->propertyAccessor->isReadable($data, $key) && $this->propertyAccessor->isWritable($data, $key)) {

                    $value = $this->propertyAccessor->getValue($data, $key);

                    if (is_string($value)) {
                        $value = $this->resolveReference($value);
                        $this->propertyAccessor->setValue($data, $key, $value);
                    }
                }
            }

        // Most probably an array
        } else {

            foreach ($data as $key => $value) {
                $data[$key] = $this->resolveReference($value);
            }

        }

        return $data;
    }

    /**
     * @param string $value
     *
     * @return mixed
     */
    protected function resolveReference($value)
    {
        $pattern = '%s[^%s]+%s';

        $regexp = sprintf($pattern, $this->constantMarker, $this->constantMarker, $this->constantMarker);
        $regexp .= '|' . sprintf($pattern, $this->referenceMaker, $this->referenceMaker, $this->referenceMaker);

        $matches = [];
        preg_match_all("/{$regexp}/u", $value, $matches);

        foreach($matches[0] as $match) {

            $referenceValue = $match[0] === $this->constantMarker
                ? constant(trim($match, $this->constantMarker))
                : $this->propertyAccessor->getValue(
                        $this->container,
                        trim($match, $this->referenceMaker)
                    );

            if ($referenceValue) {
                $value = str_replace($match, $referenceValue, $value);
            }
        }

        return $value;
    }
}