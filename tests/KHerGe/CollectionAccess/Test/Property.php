<?php

namespace Tests\KHerGe\CollectionAccess\Test;

/**
 * Uses a public property to access the collection.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Property
{
    /**
     * The collection of values.
     *
     * @var mixed[]
     */
    public $values;

    /**
     * Initializes the new instance.
     *
     * @param array $values The collection of values.
     */
    public function __construct(array $values = [])
    {
        $this->values = $values;
    }
}
