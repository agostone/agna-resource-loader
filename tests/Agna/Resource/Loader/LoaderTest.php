<?php

namespace Agna\Resource\Loader;

use Agna\Resource\Loader\Reader\File;
use PHPUnit\Framework\TestCase;

/**
 * LoaderTest
 *
 * @package Agna\Resource\Loader
 * @author Agoston Nagy <agoston.nagy@use.github.please>
 */
class LoaderTest extends TestCase
{
    public function testAddGetRemoveReader()
    {
        $fileReader = new File();
        $loader = new Loader();

        $loader->addReader($fileReader);
        $this->assertInstanceOf(File::class, $loader->getReader(File::class));

        $loader->removeReader($fileReader);
        $this->assertNull($loader->getReader(File::class));
    }

    public function testListResources()
    {
        $location = __DIR__ . '/../../../fixtures/file';
        $fileList = glob($location . '/*.*');

        $fileReader = new File();
        $loader = new Loader($fileReader);

        $resouceList = $loader->listResources($location);

        $this->assertEquals(count($fileList), count($resouceList));

        foreach ($resouceList as $resource) {
            $this->assertTrue(in_array($resource->getDirname() . '/' . $resource->getBaseName(), $fileList));
        }
    }

    public function testLoad()
    {
        $location = __DIR__ . '/../../../fixtures/file/test1.txt';

        $fileReader = new File();
        $loader = new Loader($fileReader);

        $resource = $loader->load($location);
        $content = file_get_contents($location);

        $this->assertEquals($content, $resource->getData());
    }
}