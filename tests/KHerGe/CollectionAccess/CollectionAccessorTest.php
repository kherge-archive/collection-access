<?php

namespace Tests\KHerGe\CollectionAccess;

use KHerGe\CollectionAccess\CollectionAccessor;
use KHerGe\CollectionAccess\Exception\CollectionNotExistException;
use KHerGe\CollectionAccess\Exception\InvalidCollectionException;
use KHerGe\CollectionAccess\Exception\InvalidSourceException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_TestCase as TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Tests\KHerGe\CollectionAccess\Test\Magic;
use Tests\KHerGe\CollectionAccess\Test\Method;
use Tests\KHerGe\CollectionAccess\Test\Property;

/**
 * Verifies that the collection accessor functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \KHerGe\CollectionAccess\CollectionAccessor
 */
class CollectionAccessorTest extends TestCase
{
    // (miscellaneous) ---------------------------------------------------------

    /**
     * Verify that an exception is thrown for an invalid "add" source.
     */
    public function testThrowAnExceptionForAnInvalidAddSource()
    {
        $value = false;

        $this->expectException(InvalidSourceException::class);

        /** @noinspection PhpParamsInspection */
        (new CollectionAccessor())->add($value, 'invalid', 'irrelevant');
    }

    /**
     * Verify that an exception is thrown for an invalid "get" source.
     */
    public function testThrowAnExceptionForAnInvalidGetSource()
    {
        $value = false;

        $this->expectException(InvalidSourceException::class);

        /** @noinspection PhpParamsInspection */
        (new CollectionAccessor())->get($value, 'invalid');
    }

    /**
     * Verify that an exception is thrown for an invalid "has" source.
     */
    public function testThrowAnExceptionForAnInvalidHasSource()
    {
        $value = false;

        $this->expectException(InvalidSourceException::class);

        /** @noinspection PhpParamsInspection */
        (new CollectionAccessor())->has($value, 'invalid', 'irrelevant');
    }

    /**
     * Verify that an exception is thrown for an invalid "remove" source.
     */
    public function testThrowAnExceptionForAnInvalidRemoveSource()
    {
        $value = false;

        $this->expectException(InvalidSourceException::class);

        /** @noinspection PhpParamsInspection */
        (new CollectionAccessor())->remove($value, 'invalid', 'irrelevant');
    }

    /**
     * Verify that an exception is thrown for an invalid "set" source.
     */
    public function testThrowAnExceptionForAnInvalidSetSource()
    {
        $value = false;

        $this->expectException(InvalidSourceException::class);

        /** @noinspection PhpParamsInspection */
        (new CollectionAccessor())->set($value, 'invalid', ['irrelevant']);
    }

    /**
     * Verify that an exception is thrown if the collection does not exist in an array.
     */
    public function testThrowAnExceptionIfTheCollectionDoesNotExistInTheArray()
    {
        $accessor = new CollectionAccessor(null, true);
        $source = [];

        $this->expectException(CollectionNotExistException::class);

        $accessor->add($source, 'invalid', 'irrelevant');
    }

    /**
     * Verify that the collection is created if it does not exist.
     */
    public function testCreateTheCollection()
    {
        $accessor = new CollectionAccessor();
        $source = [];

        $accessor->add($source, 'collection', 'value');

        self::assertEquals(
            ['collection' => ['value']],
            $source,
            'The collection was not created.'
        );
    }

    /**
     * Verify that an invalid collection throws an exception.
     */
    public function testThrowAnExceptionForAnInvalidCollection()
    {
        $accessor = new CollectionAccessor();
        $source = ['values' => $this];

        $this->expectException(InvalidCollectionException::class);

        $accessor->add($source, 'values', 'irrelevant');
    }

    // add() -------------------------------------------------------------------

    /**
     * Returns the test conditions and assertions for adding a value.
     *
     * @return array The conditions and assertions.
     */
    public function getAddTest()
    {
        return [

            // #0
            [
                function () {
                    return null;
                },
                ['values' => []],
                function ($source) {
                    self::assertContains(
                        123,
                        $source['values'],
                        'The value was not added.'
                    );
                }
            ],

            // #1
            [
                function () {
                    $pool = $this->createCachePool();
                    $item = $this->createCacheItem(
                        $pool,
                        sprintf('a;%s<values>', Magic::class)
                    );

                    $item
                        ->expects(self::once())
                        ->method('isHit')
                        ->willReturn(false)
                    ;

                    $item
                        ->expects(self::once())
                        ->method('set')
                        ->with(
                            [
                                'method' => 'addValue',
                                'property' => false
                            ]
                        )
                        ->willReturn($item)
                    ;

                    return $pool;
                },
                new Magic(),
                function (Magic $source) {
                    self::assertTrue(
                        $source->hasValue(123),
                        'The value was not added.'
                    );
                }
            ],

            // #2
            [
                function () {
                    $pool = $this->createCachePool();
                    $item = $this->createCacheItem(
                        $pool,
                        sprintf('a;%s<values>', Method::class)
                    );

                    $item
                        ->expects(self::once())
                        ->method('isHit')
                        ->willReturn(false)
                    ;

                    $item
                        ->expects(self::once())
                        ->method('set')
                        ->with(
                            [
                                'method' => 'addValue',
                                'property' => false
                            ]
                        )
                        ->willReturn($item)
                    ;

                    return $pool;
                },
                new Method(),
                function (Method $source) {
                    self::assertTrue(
                        $source->hasValue(123),
                        'The value was not added.'
                    );
                }
            ],

            // #3
            [
                function () {
                    $pool = $this->createCachePool();
                    $item = $this->createCacheItem(
                        $pool,
                        sprintf('a;%s<values>', Property::class)
                    );

                    $item
                        ->expects(self::once())
                        ->method('isHit')
                        ->willReturn(false)
                    ;

                    $item
                        ->expects(self::once())
                        ->method('set')
                        ->with(
                            [
                                'method' => 'addValue',
                                'property' => true
                            ]
                        )
                        ->willReturn($item)
                    ;

                    return $pool;
                },
                new Property(),
                function (Property $source) {
                    self::assertContains(
                        123,
                        $source->values,
                        'The value was not added.'
                    );
                }
            ]

        ];
    }

    /**
     * @dataProvider getAddTest
     *
     * Verify that a value is added to the collection.
     *
     * @param callable     $pool   The mock cache item pool generator.
     * @param array|object $source The collection source.
     * @param callable     $assert The result assertions.
     */
    public function testAddAValue(callable $pool, $source, callable $assert)
    {
        $accessor = $this->createAccessor($pool());
        $accessor->add($source, 'values', 123);

        $assert($source);
    }

    // get() -------------------------------------------------------------------

    /**
     * Returns the test conditions and assertions for getting values.
     *
     * @return array The conditions and assertions.
     */
    public function getGetTest()
    {
        return [

            // #0
            [
                function () {
                    return null;
                },
                ['values' => [123]],
                function ($values) {
                    self::assertEquals(
                        [123],
                        $values,
                        'The values were not returned.'
                    );
                }
            ],

            // #1
            [
                function () {
                    $pool = $this->createCachePool();
                    $item = $this->createCacheItem(
                        $pool,
                        sprintf('g;%s<values>', Magic::class)
                    );

                    $item
                        ->expects(self::once())
                        ->method('isHit')
                        ->willReturn(false)
                    ;

                    $item
                        ->expects(self::once())
                        ->method('set')
                        ->with(
                            [
                                'method' => 'getValues',
                                'property' => false
                            ]
                        )
                        ->willReturn($item)
                    ;

                    return $pool;
                },
                new Magic([123]),
                function ($values) {
                    self::assertEquals(
                        [123],
                        $values,
                        'The values were not returned.'
                    );
                }
            ],

            // #2
            [
                function () {
                    $pool = $this->createCachePool();
                    $item = $this->createCacheItem(
                        $pool,
                        sprintf('g;%s<values>', Method::class)
                    );

                    $item
                        ->expects(self::once())
                        ->method('isHit')
                        ->willReturn(false)
                    ;

                    $item
                        ->expects(self::once())
                        ->method('set')
                        ->with(
                            [
                                'method' => 'getValues',
                                'property' => false
                            ]
                        )
                        ->willReturn($item)
                    ;

                    return $pool;
                },
                new Method([123]),
                function ($values) {
                    self::assertEquals(
                        [123],
                        $values,
                        'The values were not returned.'
                    );
                }
            ],

            // #3
            [
                function () {
                    $pool = $this->createCachePool();
                    $item = $this->createCacheItem(
                        $pool,
                        sprintf('g;%s<values>', Property::class)
                    );

                    $item
                        ->expects(self::once())
                        ->method('isHit')
                        ->willReturn(false)
                    ;

                    $item
                        ->expects(self::once())
                        ->method('set')
                        ->with(
                            [
                                'method' => 'getValues',
                                'property' => true
                            ]
                        )
                        ->willReturn($item)
                    ;

                    return $pool;
                },
                new Property([123]),
                function ($values) {
                    self::assertEquals(
                        [123],
                        $values,
                        'The values were not returned.'
                    );
                }
            ],

            // #4
            [
                function () {
                    $pool = $this->createCachePool();
                    $item = $this->createCacheItem(
                        $pool,
                        sprintf('g;%s<values>', Method::class)
                    );

                    $item
                        ->expects(self::once())
                        ->method('isHit')
                        ->willReturn(false)
                    ;

                    $item
                        ->expects(self::once())
                        ->method('set')
                        ->with(
                            [
                                'method' => 'getValues',
                                'property' => false
                            ]
                        )
                        ->willReturn($item)
                    ;

                    return $pool;
                },
                new Method(
                    [
                        123,
                        456,
                        789
                    ]
                ),
                function ($values) {
                    self::assertEquals(
                        [456],
                        $values,
                        'The values were not returned.'
                    );
                },
                [1, 1]
            ],

        ];
    }

    /**
     * @dataProvider getGetTest
     *
     * Verify that the values are retrieved from the collection.
     *
     * @param callable     $pool      The mock cache item pool generator.
     * @param array|object $source    The collection source.
     * @param callable     $assert    The result assertions.
     * @param mixed[]      $arguments The getter arguments.
     */
    public function testGetTheValues(
        callable $pool,
        $source,
        callable $assert,
        array $arguments = []
    ) {
        $accessor = $this->createAccessor($pool());

        $assert($accessor->get($source, 'values', ...$arguments));
    }

    // has() -------------------------------------------------------------------

    /**
     * Returns the test conditions and assertions for check values.
     *
     * @return array The conditions and assertions.
     */
    public function getHasTest()
    {
        return [

            // #0
            [
                function () {
                    return null;
                },
                ['values' => [123]],
                function ($has) {
                    self::assertTrue(
                        $has,
                        'The value should be in the collection.'
                    );
                }
            ],

            // #1
            [
                function () {
                    $pool = $this->createCachePool();
                    $item = $this->createCacheItem(
                        $pool,
                        sprintf('h;%s<values>', Magic::class)
                    );

                    $item
                        ->expects(self::once())
                        ->method('isHit')
                        ->willReturn(false)
                    ;

                    $item
                        ->expects(self::once())
                        ->method('set')
                        ->with(
                            [
                                'method' => 'hasValue',
                                'property' => false
                            ]
                        )
                        ->willReturn($item)
                    ;

                    return $pool;
                },
                new Magic([123]),
                function ($has) {
                    self::assertTrue(
                        $has,
                        'The value should be in the collection.'
                    );
                }
            ],

            // #2
            [
                function () {
                    $pool = $this->createCachePool();
                    $item = $this->createCacheItem(
                        $pool,
                        sprintf('h;%s<values>', Method::class)
                    );

                    $item
                        ->expects(self::once())
                        ->method('isHit')
                        ->willReturn(false)
                    ;

                    $item
                        ->expects(self::once())
                        ->method('set')
                        ->with(
                            [
                                'method' => 'hasValue',
                                'property' => false
                            ]
                        )
                        ->willReturn($item)
                    ;

                    return $pool;
                },
                new Method([123]),
                function ($has) {
                    self::assertTrue(
                        $has,
                        'The value should be in the collection.'
                    );
                }
            ],

            // #3
            [
                function () {
                    $pool = $this->createCachePool();
                    $item = $this->createCacheItem(
                        $pool,
                        sprintf('h;%s<values>', Property::class)
                    );

                    $item
                        ->expects(self::once())
                        ->method('isHit')
                        ->willReturn(false)
                    ;

                    $item
                        ->expects(self::once())
                        ->method('set')
                        ->with(
                            [
                                'method' => 'hasValue',
                                'property' => true
                            ]
                        )
                        ->willReturn($item)
                    ;

                    return $pool;
                },
                new Property([123]),
                function ($has) {
                    self::assertTrue(
                        $has,
                        'The value should be in the collection.'
                    );
                }
            ]

        ];
    }

    /**
     * @dataProvider getHasTest
     *
     * Verify that a value is in the collection.
     *
     * @param callable     $pool   The mock cache item pool generator.
     * @param array|object $source The collection source.
     * @param callable     $assert The result assertions.
     */
    public function testHasAValue(callable $pool, $source, callable $assert)
    {
        $accessor = $this->createAccessor($pool());

        $assert($accessor->has($source, 'values', 123));
    }

    // remove() ----------------------------------------------------------------

    /**
     * Returns the test conditions and assertions for removing values.
     *
     * @return array The conditions and assertions.
     */
    public function getRemoveTest()
    {
        return [

            // #0
            [
                function () {
                    return null;
                },
                ['values' => [123]],
                function ($source) {
                    self::assertNotContains(
                        123,
                        $source['values'],
                        'The value was not removed.'
                    );
                }
            ],

            // #1
            [
                function () {
                    $pool = $this->createCachePool();
                    $item = $this->createCacheItem(
                        $pool,
                        sprintf('r;%s<values>', Magic::class)
                    );

                    $item
                        ->expects(self::once())
                        ->method('isHit')
                        ->willReturn(false)
                    ;

                    $item
                        ->expects(self::once())
                        ->method('set')
                        ->with(
                            [
                                'method' => 'removeValue',
                                'property' => false
                            ]
                        )
                        ->willReturn($item)
                    ;

                    return $pool;
                },
                new Magic([123]),
                function (Magic $source) {
                    self::assertFalse(
                        $source->hasValue(123),
                        'The values was not removed.'
                    );
                }
            ],

            // #2
            [
                function () {
                    $pool = $this->createCachePool();
                    $item = $this->createCacheItem(
                        $pool,
                        sprintf('r;%s<values>', Method::class)
                    );

                    $item
                        ->expects(self::once())
                        ->method('isHit')
                        ->willReturn(false)
                    ;

                    $item
                        ->expects(self::once())
                        ->method('set')
                        ->with(
                            [
                                'method' => 'removeValue',
                                'property' => false
                            ]
                        )
                        ->willReturn($item)
                    ;

                    return $pool;
                },
                new Method([123]),
                function (Method $source) {
                    self::assertFalse(
                        $source->hasValue(123),
                        'The values was not removed.'
                    );
                }
            ],

            // #3
            [
                function () {
                    $pool = $this->createCachePool();
                    $item = $this->createCacheItem(
                        $pool,
                        sprintf('r;%s<values>', Property::class)
                    );

                    $item
                        ->expects(self::once())
                        ->method('isHit')
                        ->willReturn(false)
                    ;

                    $item
                        ->expects(self::once())
                        ->method('set')
                        ->with(
                            [
                                'method' => 'removeValue',
                                'property' => true
                            ]
                        )
                        ->willReturn($item)
                    ;

                    return $pool;
                },
                new Property([123]),
                function (Property $source) {
                    self::assertNotContains(
                        123,
                        $source->values,
                        'The values was not removed.'
                    );
                }
            ]

        ];
    }

    /**
     * @dataProvider getRemoveTest
     *
     * Verify that the value is removed from the collection.
     *
     * @param callable     $pool   The mock cache item pool generator.
     * @param array|object $source The collection source.
     * @param callable     $assert The result assertions.
     */
    public function testRemoveTheValue(callable $pool, $source, callable $assert)
    {
        $accessor = $this->createAccessor($pool());
        $accessor->remove($source, 'values', 123);

        $assert($source);
    }

    // set() -------------------------------------------------------------------

    /**
     * Returns the test conditions and assertions for setting values.
     *
     * @return array The conditions and assertions.
     */
    public function getSetTest()
    {
        return [

            // #0
            [
                function () {
                    return null;
                },
                ['values' => [123]],
                function ($source) {
                    self::assertEquals(
                        [456],
                        $source['values'],
                        'The values were not replaced.'
                    );
                }
            ],

            // #1
            [
                function () {
                    $pool = $this->createCachePool();
                    $item = $this->createCacheItem(
                        $pool,
                        sprintf('s;%s<values>', Magic::class)
                    );

                    $item
                        ->expects(self::once())
                        ->method('isHit')
                        ->willReturn(false)
                    ;

                    $item
                        ->expects(self::once())
                        ->method('set')
                        ->with(
                            [
                                'method' => 'setValues',
                                'property' => false
                            ]
                        )
                        ->willReturn($item)
                    ;

                    return $pool;
                },
                new Magic([123]),
                function (Magic $source) {
                    self::assertEquals(
                        [456],
                        $source->getValues(),
                        'The values were not replaced.'
                    );
                }
            ],

            // #2
            [
                function () {
                    $pool = $this->createCachePool();
                    $item = $this->createCacheItem(
                        $pool,
                        sprintf('s;%s<values>', Method::class)
                    );

                    $item
                        ->expects(self::once())
                        ->method('isHit')
                        ->willReturn(false)
                    ;

                    $item
                        ->expects(self::once())
                        ->method('set')
                        ->with(
                            [
                                'method' => 'setValues',
                                'property' => false
                            ]
                        )
                        ->willReturn($item)
                    ;

                    return $pool;
                },
                new Method([123]),
                function (Method $source) {
                    self::assertEquals(
                        [456],
                        $source->getValues(),
                        'The values were not replaced.'
                    );
                }
            ],

            // #3
            [
                function () {
                    $pool = $this->createCachePool();
                    $item = $this->createCacheItem(
                        $pool,
                        sprintf('s;%s<values>', Property::class)
                    );

                    $item
                        ->expects(self::once())
                        ->method('isHit')
                        ->willReturn(false)
                    ;

                    $item
                        ->expects(self::once())
                        ->method('set')
                        ->with(
                            [
                                'method' => 'setValues',
                                'property' => true
                            ]
                        )
                        ->willReturn($item)
                    ;

                    return $pool;
                },
                new Property([123]),
                function (Property $source) {
                    self::assertEquals(
                        [456],
                        $source->values,
                        'The values were not replaced.'
                    );
                }
            ]

        ];
    }

    /**
     * @dataProvider getSetTest
     *
     * Verify that the value are replaced in the collection.
     *
     * @param callable     $pool   The mock cache item pool generator.
     * @param array|object $source The collection source.
     * @param callable     $assert The result assertions.
     */
    public function testReplaceTheValues(
        callable $pool,
        $source,
        callable $assert
    ) {
        $accessor = $this->createAccessor($pool());
        $accessor->set($source, 'values', [456]);

        $assert($source);
    }

    // -------------------------------------------------------------------------

    /**
     * Creates a new instance of the collection accessor.
     *
     * @param CacheItemPoolInterface|MockObject $pool The cache item pool.
     *
     * @return CollectionAccessor The new collection accessor.
     */
    private function createAccessor($pool) : CollectionAccessor
    {
        return new CollectionAccessor($pool, false, true);
    }

    /**
     * Creates a new mock cache item.
     *
     * @param CacheItemPoolInterface|MockObject $pool The mock cache pool.
     * @param string                            $key  The item key.
     *
     * @return CacheItemInterface|MockObject The mock cache item.
     */
    private function createCacheItem($pool, string $key) : CacheItemInterface
    {
        $item = $this->createMock(CacheItemInterface::class);

        $pool
            ->expects(self::once())
            ->method('getItem')
            ->with($key)
            ->willReturn($item)
        ;

        $pool
            ->expects(self::once())
            ->method('save')
            ->with($item)
        ;

        return $item;
    }

    /**
     * Creates a new mock cache pool.
     *
     * @return CacheItemPoolInterface|MockObject The mock cache pool.
     */
    private function createCachePool() : CacheItemPoolInterface
    {
        return $this->createMock(CacheItemPoolInterface::class);
    }
}
