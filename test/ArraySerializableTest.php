<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Hydrator;

use PHPUnit\Framework\TestCase;
use Zend\Hydrator\Exception\BadMethodCallException;
use Zend\Hydrator\ArraySerializable;
use ZendTest\Hydrator\TestAsset\ArraySerializable as ArraySerializableAsset;
use ZendTest\Hydrator\TestAsset\ArraySerializableNoGetArrayCopy as ArraySerializableAssetNoGetArrayCopy;

/**
 * Unit tests for {@see ArraySerializable}
 *
 * @covers \Zend\Hydrator\ArraySerializable
 */
class ArraySerializableTest extends TestCase
{
    use HydratorTestTrait;

    /**
     * @var ArraySerializable
     */
    protected $hydrator;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->hydrator = new ArraySerializable();
    }

    /**
     * Verify that we get an exception when trying to extract on a non-object
     */
    public function testHydratorExtractThrowsExceptionOnNonObjectParameter()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage(
            'Zend\Hydrator\ArraySerializable::extract expects the provided object to implement getArrayCopy()'
        );
        $this->hydrator->extract('thisIsNotAnObject');
    }

    /**
     * Verify that we get an exception when trying to hydrate a non-object
     */
    public function testHydratorHydrateThrowsExceptionOnNonObjectParameter()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage(
            'Zend\Hydrator\ArraySerializable::hydrate expects the provided object to implement'
            . ' exchangeArray() or populate()'
        );
        $this->hydrator->hydrate(['some' => 'data'], 'thisIsNotAnObject');
    }

    /**
     * Verifies that we can extract from an ArraySerializableInterface
     */
    public function testCanExtractFromArraySerializableObject()
    {
        $this->assertSame(
            [
                'foo'   => 'bar',
                'bar'   => 'foo',
                'blubb' => 'baz',
                'quo'   => 'blubb',
            ],
            $this->hydrator->extract(new ArraySerializableAsset())
        );
    }

    /**
     * Verifies we can hydrate an ArraySerializableInterface
     */
    public function testCanHydrateToArraySerializableObject()
    {
        $data = [
            'foo'   => 'bar1',
            'bar'   => 'foo1',
            'blubb' => 'baz1',
            'quo'   => 'blubb1',
        ];
        $object = $this->hydrator->hydrate($data, new ArraySerializableAsset());

        $this->assertSame($data, $object->getArrayCopy());
    }

    /**
     * Verifies that when an object already has properties,
     * these properties are preserved when it's hydrated with new data
     * existing properties should get overwritten
     */
    public function testWillPreserveOriginalPropsAtHydration()
    {
        $original = new ArraySerializableAsset();

        $data = [
            'bar' => 'foo1'
        ];

        $expected = array_merge($original->getArrayCopy(), $data);

        $actual = $this->hydrator->hydrate($data, $original);

        $this->assertSame($expected, $actual->getArrayCopy());
    }

    /**
     * To preserve backwards compatibility, if getArrayCopy() is not implemented
     * by the to-be hydrated object, simply exchange the array
     */
    public function testWillReplaceArrayIfNoGetArrayCopy()
    {
        $original = new ArraySerializableAssetNoGetArrayCopy();

        $data = [
            'bar' => 'foo1'
        ];

        $expected = $data;

        $actual = $this->hydrator->hydrate($data, $original);
        $this->assertSame($expected, $actual->getData());
    }
}
