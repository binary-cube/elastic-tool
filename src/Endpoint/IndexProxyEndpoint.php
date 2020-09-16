<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool\Endpoint;

use BinaryCube\ElasticTool\Config;
use BinaryCube\ElasticTool\Connection;

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
        $params = Config::make(static::DEFAULTS)->mergeWith($params)->toArray();

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
