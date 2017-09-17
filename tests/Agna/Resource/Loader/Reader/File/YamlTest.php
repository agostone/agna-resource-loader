<?php

namespace Agna\Resource\Loader\Reader\File;

use Agna\Resource\Loader\Resource\File\Yaml as YamlResource;
use PHPUnit\Framework\TestCase;

/**
 * YamlTest
 *
 * @package Agna\Resource\Loader\Reader
 * @author Agoston Nagy <agoston.nagy@use.github.please>
 */
class YamlTest extends TestCase
{
    protected $location = __DIR__ . '/../../../../../fixtures/file';
    protected $file = __DIR__ . '/../../../../../fixtures/file/test1.yaml';


    public function testGetSupportedExtensions()
    {
        $yamlReader = new Yaml();
        $this->assertEquals(
            [
                'yml',
                'yaml'
            ],
            $yamlReader->getSupportedExtensions()
        );
    }

    public function testListResources()
    {
        $yamlReader = new Yaml();
        $rawResources = glob(realpath($this->location) . '/*.y{a,}ml', GLOB_BRACE);
        $resources = $yamlReader->listResources($this->location);

        $this->assertTrue(count($resources) > 0);

        foreach ($resources as $resource) {
            $this->assertTrue(in_array($resource->getLocation(), $rawResources));
        }

        $this->assertEquals(count($rawResources), count($resources));
    }

    public function testIsValidSource()
    {
        $yamlReader = new Yaml();
        $this->assertTrue($yamlReader->isValidSource($this->file));
    }

    public function testCreateResource()
    {
        $yamlReader = new Yaml();
        $resource = $yamlReader->createResource($this->file);

        $this->assertInstanceOf(YamlResource::class, $resource);
        $this->assertEquals(basename($this->file), $resource->getBasename());

        return $resource;
    }

    /**
     * @depends testCreateResource
     */
    public function testLoad(YamlResource $resource)
    {
        $yamlReader = new Yaml();
        $resource = $yamlReader->load($resource);

        $data = $resource->getData();

        $this->assertEquals('value1', $data['test1']);
        $this->assertEquals('value2', $data['test2']);
    }

    /**
     * @depends testCreateResource
     */
    public function testIsValidResource(YamlResource $resource)
    {
        $yamlReader = new Yaml();
        $this->assertTrue($yamlReader->isValidResource($resource));
    }

    public function testGetSupportedType()
    {
        $yamlReader = new Yaml();
        $this->assertEquals(YamlResource::TYPE_YAML_FILE, $yamlReader->getSupportedType());
    }
}