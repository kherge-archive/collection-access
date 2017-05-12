<?php

namespace Tests\KHerGe\CollectionAccess\Test;

use BadMethodCallException;

/**
 * Uses a magic method to access the collection.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @method void addValue(mixed $value)
 * @method array getValues()
 * @method bool hasValue(mixed $value)
 * @method void removeValue(mixed $value)
 * @method void setValues(array $values)
 */
class Magic
{
    /**
     * The supported accessors.
     *
     * @var array
     */
    private static $accessors = [
        'add' => 3,
        'get' => 3,
        'has' => 3,
        'remove' => 6,
        'set' => 3
    ];

    /**
     * The collection of values.
     *
     * @var mixed[]
     */
    private $values;

    /**
     * Initializes the new instance.
     *
     * @param array $values The collection of values.
     */
    public function __construct(array $values = [])
    {
        $this->values = $values;
    }

    /**
     * Provides access to inaccessible properties.
     *
     * @param string $method    The name of the method.
     * @param array  $arguments The method arguments.
     *
     * @return mixed The method result, if any.
     */
    public function __call(string $method, array $arguments)
    {
        foreach (self::$accessors as $prefix => $length) {
            if (substr($method, 0, $length) === $prefix) {
                $property = lcfirst(substr($method, $length));

                if (in_array($prefix, ['add', 'has', 'remove'])) {
                    $property .= 's';
                }

                return $this->{$prefix}($property, ...$arguments);
            }
        }

        throw new BadMethodCallException(
            'The method "%s" does not exist for "%s".',
            $method,
            get_class($this)
        );
    }

    /**
     * Adds a value to an array accessible property.
     *
     * @param string $property The name of the property.
     * @param mixed  $value    The value to add.
     */
    private function add(string $property, $value) : void
    {
        $this->{$property}[] = $value;
    }

    /**
     * Returns the values for a property.
     *
     * @param string $property The name of the property.
     *
     * @return mixed[] The values.
     */
    private function get(string $property)
    {
        return $this->{$property};
    }

    /**
     * Checks if a value is in an array accessible property.
     *
     * @param string $property The name of the property.
     * @param mixed  $value    The value to check for.
     *
     * @return boolean Returns `true` if it is or `false` if not.
     */
    private function has(string $property, $value) : bool
    {
        return in_array($value, (array) $this->{$property}, true);
    }

    /**
     * Removes a value from an array accessible property.
     *
     * @param string $property The name of the property.
     * @param mixed  $value    The value to remove.
     */
    private function remove(string $property, $value) : void
    {
        $indexes = array_keys((array) $this->{$property}, $value, true);

        if (!empty($indexes)) {
            unset($this->{$property}[$indexes[0]]);
        }
    }

    /**
     * Replaces the values in an array accessible property.
     *
     * @param string  $property The name of the property.
     * @param mixed[] $values   The values to replace with.
     */
    private function set(string $property, array $values) : void
    {
        $this->{$property} = $values;
    }
}
