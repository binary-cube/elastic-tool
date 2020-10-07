<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool\Collection;

use BinaryCube\ElasticTool\Mapping;
use BinaryCube\ElasticTool\Support\Collection;

/**
 * Class MappingRepository
 *
 * @method Mapping[]     __invoke()
 * @method $this         put(string $id, Mapping $item)
 * @method $this         forget(string $id)
 * @method Mapping       get(string $id, $default = null)
 * @method Mapping|mixed getIfSet(string $id, $default = null)
 * @method Mapping[]     all()()
 * @method $this         clear()
 * @method bool          has(string $id)
 */
class MappingRepository extends Collection
{
    //
}
