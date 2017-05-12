<?php

namespace KHerGe\CollectionAccess\Exception;

/**
 * An exception that is thrown for an invalid source.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class InvalidSourceException extends CollectionAccessorException
{
    /**
     * Creates a new exception for an invalid source.
     *
     * @param mixed $source The invalid source.
     *
     * @return InvalidSourceException The new exception.
     */
    public static function with($source) : self
    {
        return new self(
            sprintf(
                'Expected an array or object, received a %s.',
                gettype($source)
            )
        );
    }
}
