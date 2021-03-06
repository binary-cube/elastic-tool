<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool\Endpoint;

use BinaryCube\ElasticTool\Connection;
use BinaryCube\ElasticTool\Support\Collection;

/**
 * Class IndexProxyEndpoint
 */
final class IndexProxyEndpoint extends Endpoint
{
    /**
     * @const array Default endpoint parameters
     */
    const DEFAULTS = [];

    /**
     * @var string
     */
    protected $methodName;

    /**
     * @param string $methodName
     *
     * @return $this
     */
    public function viaMethod(string $methodName)
    {
        $this->methodName = $methodName;

        return $this;
    }

    /**
     * @param null            $params
     * @param Connection|null $connection
     *
     * @return array|mixed
     */
    public function execute($params = null, Connection $connection = null)
    {
        $params = empty($params) ? [] : (array) $params;
        $params = Collection::make(static::DEFAULTS)->merge($params)->all();

        $params['index'] = $this->index->name();

        $viaMethod = $this->methodName;

        return (
            $this
                ->connection($connection)
                ->client()
                ->indices()
                ->$viaMethod($params)
        );
    }

}
