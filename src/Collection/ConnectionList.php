<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool\Collection;

use BinaryCube\ElasticTool\Connection;

/**
 * Class ConnectionList
 *
 * @method Connection[]      __invoke()
 * @method $this            set(string $id, Connection $item)
 * @method $this            remove(string $id)
 * @method Connection       get(string $id)
 * @method Connection|mixed getIfSet(string $id, $default = null)
 * @method Connection[]     all()()
 * @method $this            clear()
 * @method bool             has(string $id)
 */
class ConnectionList extends BaseList
{
    //
}
