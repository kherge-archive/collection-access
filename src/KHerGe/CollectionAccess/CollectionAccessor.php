<?php

namespace KHerGe\CollectionAccess;

use ArrayAccess;
use Doctrine\Common\Inflector\Inflector;
use KHerGe\CollectionAccess\Exception\CollectionNotExistException;
use KHerGe\CollectionAccess\Exception\InvalidCollectionException;
use KHerGe\CollectionAccess\Exception\InvalidSourceException;
use ReflectionClass;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Manages access to values in a collection.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class CollectionAccessor implements CollectionAccessorInterface
{
    /**
     * The key used for the add-er accessor.
     *
     * @var string
     */
    const ADD = 'a';

    /**
     * The key used for the get-ter accessor.
     *
     * @var string
     */
    const GET = 'g';

    /**
     * The key used for the has-ser accessor.
     *
     * @var string
     */
    const HAS = 'h';

    /**
     * The key used for the remove-r accessor.
     *
     * @var string
     */
    const REMOVE = 'r';

    /**
     * The key used for the set-ter accessor.
     *
     * @var string
     */
    const SET = 's';

    /**
     * The cache item pool.
     *
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * Throw an exception if the collection does not exist?
     *
     * @var boolean
     */
    private $exception;

    /**
     * Allow the use of magic methods (i.e. `__call`, `__get`, `__set`)?
     *
     * @var boolean
     */
    private $magic;

    /**
     * The access method name templates.
     *
     * @var array[]
     */
    private $methods;

    /**
     * The pluralized accessors.
     *
     * @var bool[]
     */
    private static $pluralize = [
        self::ADD => false,
        self::GET => true,
        self::HAS => false,
        self::REMOVE => false,
        self::SET => true
    ];

    /**
     * Initializes the collection accessor.
     *
     * @param CacheItemPoolInterface|null $cache     The cache item pool.
     * @param boolean                     $exception Throw an exception if the collection does not exist?
     * @param boolean                     $magic     Allow the use of magic methods (i.e. `__call`, `__get`, `__set`)?
     * @param array                       $methods   The access method name templates.
     */
    public function __construct(
        CacheItemPoolInterface $cache = null,
        bool $exception = false,
        bool $magic = false,
        array $methods = [
            self::ADD => ['add%s', 'assign%s'],
            self::GET => ['get%s'],
            self::HAS => ['has%s', 'contains%s'],
            self::REMOVE => ['remove%s', 'unassign%s'],
            self::SET => ['set%s', 'replace%s']
        ]
    ) {
        $this->cache = $cache;
        $this->exception = $exception;
        $this->magic = $magic;
        $this->methods = $methods;
    }

    /**
     * {@inheritdoc}
     */
    public function add(&$source, string $collection, $value) : void
    {
        if (is_array($source)) {
            $this->checkArray($source, $collection);

            $source[$collection][] = $value;
        } elseif (is_object($source)) {
            $this->withObject($source, $collection, self::ADD, $value);
        } else {
            throw InvalidSourceException::with($source);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($source, string $collection, ...$argument) : array
    {
        if (is_array($source)) {
            $this->checkArray($source, $collection);

            return $source[$collection];
        } elseif (is_object($source)) {
            return $this->withObject(
                $source,
                $collection,
                self::GET,
                null,
                $argument
            );
        }

        throw InvalidSourceException::with($source);
    }

    /**
     * {@inheritdoc}
     */
    public function has($source, string $collection, $value) : bool
    {
        if (is_array($source)) {
            $this->checkArray($source, $collection);

            return in_array($value, (array) $source[$collection], true);
        } elseif (is_object($source)) {
            return $this->withObject(
                $source,
                $collection,
                self::HAS,
                $value
            );
        }

        throw InvalidSourceException::with($source);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(
        &$source,
        string $collection,
        $value
    ) : void {
        if (is_array($source)) {
            $this->checkArray($source, $collection);

            $indexes = array_keys((array) $source[$collection], $value, true);

            if (!empty($indexes)) {
                unset($source[$collection][$indexes[0]]);
            }
        } elseif (is_object($source)) {
            $this->withObject($source, $collection, self::REMOVE, $value);
        } else {
            throw InvalidSourceException::with($source);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set(&$source, string $collection, $values) : void
    {
        if (is_array($source)) {
            $this->checkArray($source, $collection);

            $source[$collection] = $values;
        } elseif (is_object($source)) {
            $this->withObject($source, $collection, self::SET, $values);
        } else {
            throw InvalidSourceException::with($source);
        }
    }

    /**
     * Checks that the collection exists and is valid.
     *
     * @param array  &$source    The source that has the collection.
     * @param string $collection The name of the collection.
     *
     * @throws CollectionNotExistException If the collection does not exist.
     * @throws InvalidCollectionException  If the collection is not array accessible.
     */
    private function checkArray(array &$source, string $collection) : void
    {
        if (!array_key_exists($collection, $source)) {
            if ($this->exception) {
                throw new CollectionNotExistException(
                    sprintf(
                        'A collection does not exist for the array key "%s".',
                        $collection
                    )
                );
            }

            $source[$collection] = [];
        }

        if (!is_array($source[$collection])
         && !($source[$collection] instanceof ArrayAccess)) {
            throw InvalidCollectionException::notArrayAccessible(
                null,
                $collection
            );
        }
    }

    /**
     * Finds the accessor for the collection in the object.
     *
     * @param object $source     The source that has the collection.
     * @param string $collection The name of the collection.
     * @param string $key        The accessor key.
     *
     * @return array The accessor.
     */
    private function findAccessor(
        $source,
        string $collection,
        string $key
    ) : array {
        if (null !== $this->cache) {
            $item = $this->cache->getItem(
                sprintf(
                    '%s;%s<%s>',
                    $key,
                    get_class($source),
                    $collection
                )
            );

            if ($item->isHit()) {
                return $item->get();
            }
        }

        $accessor = [
            'method' => $this->findMethod($source, $collection, $key),
            'property' => $this->isProperty($source, $collection),
        ];

        if (isset($item)) {
            $this->cache->save($item->set($accessor));
        }

        return $accessor;
    }

    /**
     * Finds the accessor method for the collection in an object.
     *
     * @param object $source     The source that has the collection.
     * @param string $collection The name of the collection.
     * @param string $key        The accessor key.
     *
     * @return null|string The name of the method.
     */
    private function findMethod(
        $source,
        string $collection,
        string $key
    ) : ?string {
        $methods = [];

        foreach ($this->methods[$key] as $method) {
            $method = sprintf(
                $method,
                ucfirst(Inflector::classify($collection))
            );

            if (self::$pluralize[$key]) {
                $method = Inflector::pluralize($method);
            } else {
                $method = Inflector::singularize($method);
            }

            $methods[] = $method;
        }

        $class = new ReflectionClass($source);

        do {
            foreach ($methods as $i => $method) {
                if ($class->hasMethod($method)) {
                    if ($class->getMethod($method)->isPublic()) {
                        return $method;
                    }

                    unset($methods[$i]);
                }
            }
        } while (($class = $class->getParentClass()));

        if ($this->magic) {
            return array_shift($methods);
        }

        return null;
    }

    /**
     * Checks if the collection exists as a public property for the object.
     *
     * @param object $source     The source that has the collection.
     * @param string $collection The name of the collection.
     *
     * @return boolean Returns `true` if it does or `false` if not.
     */
    private function isProperty($source, string $collection) : bool
    {
        $reflection = new ReflectionClass($source);

        while (!$reflection->hasProperty($collection)) {
            if (!($reflection = $reflection->getParentClass())) {
                return false;
            }
        }

        return $reflection->getProperty($collection)->isPublic();
    }

    /**
     * Invokes an accessor method for an object and returns the result.
     *
     * @param object             $source     The source that has the collection.
     * @param string             $collection The name of the collection.
     * @param string             $key        The accessor key.
     * @param mixed|mixed[]|void $value      The value(s) to pass.
     * @param mixed[]            $arguments  The method arguments.
     *
     * @return mixed The result, if any.
     *
     * @throws InvalidCollectionException  If the collection is not accessible.
     */
    private function withObject(
        $source,
        string $collection,
        string $key,
        $value = null,
        array $arguments = []
    ) {
        $accessor = $this->findAccessor($source, $collection, $key);

        if ($accessor['property']) {
            return $this->withProperty($source, $collection, $key, $value);
        } elseif (null !== $accessor['method']) {
            if (self::GET === $key) {
                return $source->{$accessor['method']}(...$arguments);
            }

            return $source->{$accessor['method']}($value);
        }

        throw InvalidCollectionException::notAccessible($source, $collection);
    }

    /**
     * Access the property of an object.
     *
     * @param object             $source     The source that has the collection.
     * @param string             $collection The name of the collection.
     * @param string             $key        The accessor key.
     * @param mixed|mixed[]|void $value      The value(s) to pass.
     *
     * @return mixed The result, if any.
     */
    private function withProperty(
        $source,
        string $collection,
        string $key,
        $value = null
    ) {
        switch ($key) {
            case self::ADD:
                $source->{$collection}[] = $value;

                break;

            case self::GET:
                return $source->{$collection};

            case self::HAS:
                return in_array(
                    $value,
                    (array) $source->{$collection},
                    true
                );

            case self::REMOVE:
                $indexes = array_keys(
                    (array) $source->{$collection},
                    $value,
                    true
                );

                if (!empty($indexes)) {
                    unset($source->{$collection}[$indexes[0]]);
                }

                break;

            case self::SET:
                $source->{$collection} = $value;

                break;
        }

        return null;
    }
}
