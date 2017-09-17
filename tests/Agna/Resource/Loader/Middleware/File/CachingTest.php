<?php

namespace Agna\Resource\Loader\Middleware\File;

use Agna\Resource\Loader\Loader;
use Agna\Resource\Loader\Middleware\File\Yaml\ImportsResolver;
use Agna\Resource\Loader\Reader\File\Yaml;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\PeekAndPoke\Proxy;

/**
 * CachingTest
 *
 * @package Agna\Resource\Loader\Middleware\File
 * @author Agoston Nagy <agoston.nagy@use.github.please>
 */
class CachingTest extends TestCase
{
    protected $tmpDirectory = __DIR__ . '/../../../../../fixtures/temp';

    public function tearDown()
    {
        $files = glob($this->tmpDirectory . '/*');

        foreach ($files as $file) {
            unlink($file);
        }

        parent::tearDown();
    }

    public function test__invoke()
    {
        $resourceFile = __DIR__ . '/../../../../../fixtures/file/test2.yml';

        $yamlReader = new Yaml();
        $loader = new Loader($yamlReader);

        $caching = new Caching($this->tmpDirectory, true);

        $loader->addMiddleware(Yaml::getName(), $caching);
        $yamlResource = $loader->load($resourceFile);
        $data = $yamlResource->getData();

        $this->assertEquals(['test1.yaml'], $data['imports']);
        $this->assertEquals('value4', $data['test2']);
        $this->assertEquals('value3', $data['test3']);

        // Reloading the resource to see cache hit (file ctime and file mtime should remain the same)
        $creationTime = $yamlResource->getCreationTime();
        $modificationTime = $yamlResource->getModificationTime();

        $yamlResource = $loader->load($resourceFile);
        $this->assertEquals($data, $yamlResource->getData());
        $this->assertEquals($creationTime, $yamlResource->getCreationTime());
        $this->assertEquals($modificationTime, $yamlResource->getModificationTime());

        // Testing caching middleware internals with peek-and-poke
        $cachingProxy = new Proxy($caching);
        $cacheFile = $cachingProxy->getCacheFile(realpath($resourceFile));
        $cacheFile = $cachingProxy->unserialize(file_get_contents($cacheFile));
        $this->assertEquals($data, $cacheFile);

        $metaFile = $cachingProxy->getCacheMetaFile(realpath($resourceFile));
        $this->assertTrue(is_file($metaFile));
    }
}