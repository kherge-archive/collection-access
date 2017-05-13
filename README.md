Collection Access
=================

Provides a uniform interface for accessing the contents of collections.

This library provides the ability to manipulate an array or array accessible
list of values inside of an array or an object. Support is provided for objects
with public properties, magic methods, and accessors (e.g. `add*()`, `get*()`, 
etc.).

Usage
-----

```php
// An example array with a collection.
$array = ['values' => []];

// An example object with a collection.
$object = new class() {
    private $values = [];
    
    public function addValue($value) : void {
        $this->values[] = $value;
    }
    
    public function getValues() : array {
        return $this->values;
    }
    
    public function hasValue($value) : bool {
        return in_array($value, $this->values, true);
    }
    
    public function removeValue($value) : void {
        $indexes = array_keys($this->values, $value, true);
        
        if (!empty($indexes)) {
            unset($this->values[$indexes[0]]);
        }
    }
    
    public function setValues(array $values) : void {
        $this->values = $values;
    }
};

// Create a new collection accessor.
$accessor = new KHerGe\CollectionAccess\CollectionAccessor();

// Create a value to use as an example.
$value = 123;

// Add the value.
$accessor->add($array, 'values', $value);
$accessor->add($object, 'values', $value);

// Get the values.
$values = $accessor->get($array, 'values');
$values = $accessor->get($object, 'values');

// Check for a value.
if ($accessor->has($array, 'values', $value)) {
    // ...
}

if ($accessor->has($object, 'values', $value)) {
    // ...
}

// Remove a value.
$accessor->remove($array, 'values', $value);
$accessor->remove($object, 'values', $value);

// Replace all values.
$accessor->set($array, 'values', $values);
$accessor->set($object, 'values', $values);
```

Requirements
------------

- PHP 7.1+
- Composer
    - Doctrine Inflector 1.0+
    - PSR Cache 1.0+

Installation
------------

    composer require kherge/collection-access

Documentation
-------------

The collection accessor is found in:

```php
use KHerGe\CollectionAccess\CollectionAccessor;
```

The constructor accepts some arguments, all having default values:

```php
public function __construct(
    CacheItemPoolInterface $cache = null,
    bool $exception = false,
    bool $magic = false,
    array $methods = [
        CollectionAccessor::ADD => ['add%s', 'assign%s'],
        CollectionAccessor::GET => ['get%s'],
        CollectionAccessor::HAS => ['has%s', 'contains%s'],
        CollectionAccessor::REMOVE => ['remove%s', 'unassign%s'],
        CollectionAccessor::SET => ['set%s', 'replace%s']
    ]
);
```

### `$cache`

This is the cache item pool that will be used by the collection accessor to
store and retrieve accessor information. This will save the accessor from
having to continuously rediscover how a collection is accessed for an object.
Any cache pool implementing PSR-6 is supported.

### `$exception`

This is a flag that is used to determine how a collection that does not exist
in a given value should be handled. If this flag is set to `true` and the
collection does not exist, an exception is thrown. Otherwise, the collection
is automatically created before manipulating it.

### `$magic`

This is a flag used to determine if the `__call` magic method should be
used if a non-magic accessor is not available.

### `$methods`

This is an array containing lists of method name templates that are to be
tried when attempting to discover an accessor for the collection. If none
of these methods exist, an attempt at using a magic method is made if the
`$magic` flag is set to true.

License
-------

This library is available under the MIT and Apache 2.0 licenses.
