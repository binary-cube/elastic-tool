<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool\Collection;

/**
 * Class Collection
 */
class Collection
{

    /**
     * @var mixed[]
     */
    protected $items = [];

    /**
     * Constructor.
     *
     * @param array $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * @param string $id
     * @param mixed  $item
     *
     * @return $this
     */
    public function set(string $id, $item): self
    {
        $this->items[$id] = $item;

        return $this;
    }

    /**
     * @param string $id
     *
     * @return $this
     */
    public function remove(string $id): self
    {
        unset($this->items[$id]);

        return $this;
    }

    /**
     * @param string $id
     *
     * @return mixed
     */
    public function get(string $id)
    {
        return $this->items[$id];
    }

    /**
     * @param string     $id
     * @param null|mixed $default
     *
     * @return mixed|null
     */
    public function getIfSet(string $id, $default = null)
    {
        if ($this->has($id) && isset($this->items[$id])) {
            return $this->items[$id];
        }

        return $default;
    }

    /**
     * @return array
     */
    public function ids(): array
    {
        return \array_keys($this->items);
    }

    /**
     * @return mixed[]
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * @return $this
     */
    public function clear(): self
    {
        $this->items = [];

        return $this;
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function has(string $id): bool
    {
        return (isset($this->items[$id]) || \array_key_exists($id, $this->items));
    }

}
