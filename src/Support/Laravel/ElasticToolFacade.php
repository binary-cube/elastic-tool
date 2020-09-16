<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool\Support\Laravel;

use Illuminate\Support\Facades\Facade;

/**
 * Class ElasticToolFacade
 */
class ElasticToolFacade extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'elastic.tool';
    }

}
