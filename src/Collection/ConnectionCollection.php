<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool\Collection;

use BinaryCube\ElasticTool\Connection;
use BinaryCube\ElasticTool\Support\Collection;

/**
 * Class ConnectionCollection
 *
 * @method Connection[]     __invoke()
 * @method $this            put(string $id, Connection $item)
 * @method $this            forget(string $id)
 * @method Connection       get(string $id, $default = null)
 * @method Connection|mixed getIfSet(string $id, $default = null)
 * @method Connection[]     all()()
 * @method $this            clear()
 * @method bool             has(string $id)
 */
class ConnectionCollection extends Collection
{
    //
}
