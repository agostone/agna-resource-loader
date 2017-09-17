<?php

namespace Agna\Resource\Loader\Middleware\Arrey;

use Agna\Resource\Loader\Loader;
use Agna\Resource\Loader\Reader\File\Yaml;
use PHPUnit\Framework\TestCase;

/**
 * ImportsResolverTest
 *
 * @package Agna\Resource\Loader\Middleware\Arrey
 * @author Agoston Nagy <agoston.nagy@use.github.please>
 */
class ImportsResolverTest extends TestCase
{
    public function test__invoke()
    {
        $yamlReader = new Yaml();
        $loader = new Loader($yamlReader);

        $yamlResource = $yamlReader->createResource(__DIR__ . '/../../../../../fixtures/file/test2.yml');
        $yamlResource = $yamlReader->load($yamlResource);

        $importsResolver = new ImportsResolver();

        $yamlResource = $importsResolver($yamlResource, $loader, function ($resource) { return $resource; });
        $yamlResource = $yamlResource->getData();

        $this->assertEquals('value1', $yamlResource['test1']);
        $this->assertEquals('value4', $yamlResource['test2']);
        $this->assertEquals('value3', $yamlResource['test3']);
    }
}