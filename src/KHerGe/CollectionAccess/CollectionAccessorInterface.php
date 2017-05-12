<?php

namespace KHerGe\CollectionAccess;

use KHerGe\CollectionAccess\Exception\CollectionNotExistException;
use KHerGe\CollectionAccess\Exception\InvalidCollectionException;
use KHerGe\CollectionAccess\Exception\InvalidSourceException;

/**
 * Defines how a collection accessor must be implemented.
 *
 * A collection accessor provides a uniform interface for adding, checking,
 * removing, and setting collection values. A collection can be contained in
 * either an `array` or an `object`.
 *
 * **Array**
 *
 * ```php
 * // Our source array containing the "example" collection.
 * $source = [
 *     'examples' => []
 * ];
 * ```
 *
 * **Object**
 *
 * ```php
 * $source = new class() {
 *     private $examples = [];
 *
 *     public function addExample($value) : void
 *     {
 *         $this->examples[] = $value;
 *     }
 *
 *     public function getExamples() : array
 *     {
 *         return $this->examples;
 *     }
 *
 *     public function hasExample($value) : bool
 *     {
 *         return in_array($value, $this->examples, true);
 *     }
 *
 *     public function removeExample($value) : void
 *     {
 *         $indexes = array_keys($this->examples, $value, true);
 *
 *         foreach ($indexes as $index) {
 *             unset($this->examples[$index]);
 *         }
 *     }
 *
 *     public function setExamples(array $values) : void
 *     {
 *         $this->examples = $values;
 *     }
 * };
 * ```
 *
 * **Accessing**
 *
 * The following examples will work for either array or object sources
 * containing the "example" collection.
 *
 * ```php
 * // The value we want to use with the collection.
 * $value = 123;
 *
 * // Add the value to the collection.
 * $accessor->add($source, 'examples', $value);
 *
 * // Check if the value is in the collection.
 * if ($accessor->has($source, 'examples', $value)) {
 *     // ...
 * }
 *
 * // Get all of the values in the collection.
 * $values = $accessor->get($source, 'examples');
 *
 * // Remove the value from the collection.
 * $accessor->remove($source, 'examples', $value);
 *
 * // Replaces all of the values in the collection.
 * $accessor->set($source, 'examples', [456, 789]);
 * ```
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
interface CollectionAccessorInterface
{
    /**
     * Adds a value to a collection.
     *
     * ```php
     * $accessor->add($source, 'examples', $value);
     * ```
     *
     * @param array|object &$source    The source that has the collection.
     * @param string       $collection The name of the collection.
     * @param mixed        $value      The value to add.
     *
     * @throws InvalidCollectionException  If the collection is not valid.
     * @throws InvalidSourceException      If the source is not valid.
     * @throws CollectionNotExistException If the collection does not exist.
     */
    public function add(&$source, string $collection, $value) : void;

    /**
     * Returns all of the values in the collection.
     *
     * ```php
     * $values = $accessor->get($source, 'examples');
     * ```
     *
     * @param array|object &$source    The source that has the collection.
     * @param string       $collection The name of the collection.
     *
     * @return mixed[] The values in the collection.
     *
     * @throws InvalidCollectionException  If the collection is not valid.
     * @throws InvalidSourceException      If the source is not valid.
     * @throws CollectionNotExistException If the collection does not exist.
     */
    public function get(&$source, string $collection) : array;

    /**
     * Checks if a collection contains a value.
     *
     * ```php
     * if ($accessor->has($source, 'examples', $value)) {
     *     // ...
     * }
     * ```
     *
     * @param array|object $source     The source that has the collection.
     * @param string       $collection The name of the collection.
     * @param mixed        $value      The value to check for.
     *
     * @return boolean Returns `true` if the value is in the collection or `false` if not.
     *
     * @throws InvalidCollectionException  If the collection is not valid.
     * @throws InvalidSourceException      If the source is not valid.
     * @throws CollectionNotExistException If the collection does not exist.
     */
    public function has($source, string $collection, $value) : bool;

    /**
     * Removes a value from the collection.
     *
     * ```php
     * $accessor->remove($source, 'examples', $value);
     * ```
     *
     * @param array|object &$source    The source that has the collection.
     * @param string       $collection The name of the collection.
     * @param mixed        $value      The value to remove.
     *
     * @throws InvalidCollectionException  If the collection is not valid.
     * @throws InvalidSourceException      If the source is not valid.
     * @throws CollectionNotExistException If the collection does not exist.
     */
    public function remove(&$source, string $collection, $value) : void;

    /**
     * Replaces all of the values in the collection.
     *
     * ```php
     * $accessor->set($source, 'examples', $values);
     * ```
     *
     * @param array|object &$source    The source that has the collection.
     * @param string       $collection The name of the collection.
     * @param mixed[]      $values     The values to replace with.
     *
     * @throws InvalidCollectionException  If the collection is not valid.
     * @throws InvalidSourceException      If the source is not valid.
     * @throws CollectionNotExistException If the collection does not exist.
     */
    public function set(&$source, string $collection, $values) : void;
}
