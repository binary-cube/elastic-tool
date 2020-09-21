<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool\Collection;

use BinaryCube\ElasticTool\Index;

/**
 * Class IndexCollection
 *
 * @method Index[]     __invoke()
 * @method $this       set(string $id, Index $item)
 * @method $this       remove(string $id)
 * @method Index       get(string $id)
 * @method Index|mixed getIfSet(string $id, $default = null)
 * @method Index[]     all()()
 * @method $this       clear()
 * @method bool        has(string $id)
 */
class IndexCollection extends Collection
{

    /**
     * Return all indices in the given group
     *
     * @param string $group
     *
     * @return IndexCollection
     */
    public function inGroup(string $group): IndexCollection
    {
        if (empty($group)) {
            return new static();
        }

        return new static(\array_filter($this->items, function ($item) use ($group) {
            return $item->group() === $group;
        }));
    }

}
