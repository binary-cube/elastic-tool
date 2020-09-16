<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool;

use BinaryCube\DotArray\DotArray;

/**
 * Class Config
 */
class Config
{

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @param array $config
     *
     * @return static
     */
    public static function make(array $config = []): self
    {
        return (new static($config));
    }

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * @param array $config
     *
     * @return $this
     */
    public function set(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return array
     */
    public function get(): array
    {
        return $this->toArray();
    }

    /**
     * If each array has an element with the same string key value, the latter
     * will overwrite the former (different from array_merge_recursive).
     * Recursive merging will be conducted if both arrays have an element of array
     * type and are having the same key.
     * For integer-keyed elements, the elements from the latter array will
     * be appended to the former array.
     *
     * @param array $a array to be merged from. You can specify additional
     *                 arrays via second argument, third argument, fourth argument etc.
     *
     * @return $this
     */
    public function mergeWith(array $a): self
    {
        $this->config = DotArray::create($this->config)->merge($a)->toArray();

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->config;
    }

}
