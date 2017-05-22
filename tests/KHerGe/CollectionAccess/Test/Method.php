<?php

namespace Tests\KHerGe\CollectionAccess\Test;

/**
 * Uses a accessor methods to access the collection.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Method
{
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
     * Adds the value to the collection.
     *
     * @param mixed $value The value to add.
     */
    public function addValue($value) : void
    {
        $this->values[] = $value;
    }

    /**
     * Returns the values in the collection.
     *
     * @param integer $offset The offset.
     * @param integer $limit  The limit.
     *
     * @return mixed[] The values.
     */
    public function getValues(int $offset = 0, int $limit = null) : array
    {
        return array_slice($this->values, $offset, $limit);
    }

    /**
     * Checks if a value is in the collection.
     *
     * @param mixed $value The value to check for.
     *
     * @return boolean Returns `true` if it is or `false` if not.
     */
    public function hasValue($value) : bool
    {
        return in_array($value, $this->values, true);
    }

    /**
     * Removes a value in the collection.
     *
     * @param mixed $value The value to remove.
     */
    public function removeValue($value) : void
    {
        $indexes = array_keys($this->values, $value, true);

        if (!empty($indexes)) {
            unset($this->values[$indexes[0]]);
        }
    }

    /**
     * Replaces the values in the collection.
     *
     * @param array $values The values to replace with.
     */
    public function setValues(array $values) : void
    {
        $this->values = $values;
    }
}
