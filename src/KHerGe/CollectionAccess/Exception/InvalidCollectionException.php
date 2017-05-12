<?php

namespace KHerGe\CollectionAccess\Exception;

/**
 * An exception thrown for an invalid collection.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class InvalidCollectionException extends CollectionAccessorException
{
    /**
     * Creates a new exception for a collection that is not accessible.
     *
     * @param object $source     The source that has the collection.
     * @param string $collection The name of the collection.
     *
     * @return InvalidCollectionException The new exception.
     */
    public static function notAccessible($source, string $collection) : self
    {
        return new self(
            sprintf(
                'The collection "%s" for "%s" is not accessible.',
                $collection,
                get_class($source)
            )
        );
    }

    /**
     * Creates a new exception for a collection that is not array accessible.
     *
     *
     * @param null|object $source     The source that has the collection.
     * @param string      $collection The name of the collection.
     *
     * @return InvalidCollectionException The new exception.
     */
    public static function notArrayAccessible($source, string $collection) :self
    {
        return new self(
            sprintf(
                'The collection "%s"%s is not array accessible.',
                $collection,
                is_object($source) ? (' for "' . get_class($source)) : '"'
            )
        );
    }
}
