<?php

namespace Agna\Resource\Loader;

use Agna\Resource\Loader\Middleware\File\Caching;
use Agna\Resource\Loader\Reader\File;
use PHPUnit\Framework\TestCase;

/**
 * LoaderBuilderTest
 *
 * @package Agna\Resource\Loader
 * @author Agoston Nagy <agoston.nagy@use.github.please>
 */
class LoaderBuilderTest extends TestCase
{

    public function testBuild()
    {
        $builder = new LoaderBuilder();

        $loader = $builder->build(
            [
                'className' => Loader::class,
                'reader' => [
                    File::class => [],
                ],
                'middlewares' => [
                    File::class => [
                        Caching::class => [
                            __DIR__ . '/../../../fixtures/temp'
                        ]
                    ]
                ]
            ]
        );

        $this->assertInstanceOf(LoaderInterface::class, $loader);

        $reader = $loader->getReader(File::class);
        $this->assertInstanceOf(File::class, $reader);

        $this->assertTrue($loader->hasMiddlewares(File::class));
    }
}