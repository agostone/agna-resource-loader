<?php

namespace Agna\Resource\Loader\Middleware;

use Agna\Resource\Loader\Loader;
use Agna\Resource\Loader\Resource\File;
use PHPUnit\Framework\TestCase;

/**
 * ReferenceResolverTest
 *
 * @package Agna\Resource\Loader\Middleware
 * @author Agoston Nagy <agoston.nagy@use.github.please>
 */
class ReferenceResolverTest extends TestCase
{
    public function test__invoke()
    {
        $fileResource = new File('', ['index' => '@[index]@']);
        $loader = new Loader($fileResource);

        // Array reference
        $arrayContainer = ['index' => 'This is an array reference'];
        $referenceResolver = new ReferenceResolver($arrayContainer);
        $fileResource = $referenceResolver($fileResource, $loader, function ($resource) { return $resource; });
        $data = $fileResource->getData();
        $this->assertEquals($arrayContainer['index'], $data['index']);

        // Property reference
        $fileResource = new File('', ['property' => '@property@/hmm/@property@']);
        $objectContainer = new \stdClass();
        $objectContainer->property = 'This is an object property!';
        $referenceResolver = new ReferenceResolver($objectContainer);
        $fileResource = $referenceResolver($fileResource, $loader, function ($resource) { return $resource; });
        $data = $fileResource->getData();
        $this->assertEquals('This is an object property!/hmm/This is an object property!', $data['property']);

        // Constant reference
        defined('CONSTANT') ?: define('CONSTANT', 'magical');
        $fileResource = new File('', ['constant' => 'The "%CONSTANT%" is a constant!']);
        $referenceResolver = new ReferenceResolver([]);
        $fileResource = $referenceResolver($fileResource, $loader, function ($resource) { return $resource; });
        $data = $fileResource->getData();
        $this->assertEquals('The "magical" is a constant!', $data['constant']);

        // UTF-8 reference
        $fileResource = new File('', ['ナ' => 'バナナ @[ナ]@ バナナ']);
        $referenceResolver = new ReferenceResolver(['ナ' => 'バナナ']);
        $fileResource = $referenceResolver($fileResource, $loader, function ($resource) { return $resource; });
        $data = $fileResource->getData();
        $this->assertEquals('バナナ バナナ バナナ', $data['ナ']);

        // Object data
        $fileResource = new File('', new ObjectDataFixture());
        $referenceResolver = new ReferenceResolver(['ナ' => 'バナナ']);
        $fileResource = $referenceResolver($fileResource, $loader, function ($resource) { return $resource; });
        $data = $fileResource->getData();
        $this->assertEquals('バナナ バナナ バナナ', $data->property);

    }
}

class ObjectDataFixture
{
    public $property = 'バナナ @[ナ]@ バナナ';
}