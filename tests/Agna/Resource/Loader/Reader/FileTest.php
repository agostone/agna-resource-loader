<?php

namespace Agna\Resource\Loader\Reader;

use Agna\Resource\Loader\Resource\File as FileResource;
use Agna\Resource\Loader\Resource\ResourceInterface;
use PHPUnit\Framework\TestCase;

/**
 * FileTest
 *
 * @package Agna\Resource\Loader\Reader
 * @author Agoston Nagy <agoston.nagy@use.github.please>
 */
class FileTest extends TestCase
{
    protected $location = __DIR__ . '/../../../../fixtures/file';
    protected $file = __DIR__ . '/../../../../fixtures/file/test1.txt';


    public function testGetSupportedExtensions()
    {
        $fileReader = new File();
        $this->assertEquals('*', $fileReader->getSupportedExtensions());
    }

    public function testIsValidLocation()
    {
        $fileReader = new File();
        $this->assertTrue($fileReader->isValidLocation($this->location));
    }

    public function testListResources()
    {
        $fileReader = new File();
        $rawResources = glob(realpath($this->location) . '/*');
        $resources = $fileReader->listResources($this->location);

        $this->assertTrue(count($resources) > 0);

        foreach ($resources as $resource) {
            $this->assertTrue(in_array($resource->getLocation(), $rawResources));
        }

        $this->assertEquals(count($rawResources), count($resources));
    }

    public function testIsValidSource()
    {
        $fileReader = new File();
        $this->assertTrue($fileReader->isValidSource($this->file));
    }

    public function testCreateResource()
    {
        $fileReader = new File();
        $resource = $fileReader->createResource($this->file);

        $this->assertInstanceOf(FileResource::class, $resource);
        $this->assertEquals(basename($this->file), $resource->getBasename());

        return $resource;
    }

    /**
     * @depends testCreateResource
     */
    public function testLoad(FileResource $resource)
    {
        $fileReader = new File();
        $resource = $fileReader->load($resource);

        $rawReadData = file_get_contents($this->file);

        $this->assertEquals($rawReadData, $resource->getData());
    }

    /**
     * @depends testCreateResource
     */
    public function testIsValidResource(FileResource $resource)
    {
        $fileReader = new File();
        $this->assertTrue($fileReader->isValidResource($resource));
    }

    public function testGetSupportedType()
    {
        $fileReader = new File();
        $this->assertEquals(FileResource::TYPE_FILE, $fileReader->getSupportedType());
    }
}