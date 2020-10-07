<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool\Collection;

use BinaryCube\ElasticTool\Index;
use BinaryCube\ElasticTool\Support\Collection;

/**
 * Class IndexRepository
 *
 * @method Index[]     __invoke()
 * @method $this       put(string $id, Index $item)
 * @method $this       forget(string $id)
 * @method Index       get(string $id, $default = null)
 * @method Index|mixed getIfSet(string $id, $default = null)
 * @method Index[]     all()()
 * @method $this       clear()
 * @method bool        has(string $id)
 */
class IndexRepository extends Collection
{

    /**
     * Return all indices in the given group
     *
     * @param string $group
     *
     * @return IndexRepository
     */
    public function inGroup(string $group): IndexRepository
    {
        if (empty($group)) {
            return new static();
        }

        return new static(\array_filter($this->items, function ($item) use ($group) {
            return $item->group() === $group;
        }));
    }

}
