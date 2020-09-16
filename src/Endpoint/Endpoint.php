<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool\Endpoint;

use Psr\Log\LoggerInterface;
use BinaryCube\ElasticTool\Index;
use BinaryCube\ElasticTool\Component;
use BinaryCube\ElasticTool\Connection;

/**
 * Class Endpoint
 */
abstract class Endpoint extends Component
{
    /**
     * @const array Default endpoint parameters
     */
    const DEFAULTS = [];

    /**
     * @var Index
     */
    protected $index;

    /**
     * Constructor.
     *
     * @param Index                $index
     * @param LoggerInterface|null $logger
     */
    public function __construct(Index $index, $logger = null)
    {
        parent::__construct(null, $logger);

        $this->index = $index;
    }

    /**
     * @param mixed           $params
     * @param Connection|null $connection
     *
     * @return mixed
     */
    abstract public function execute($params = null, Connection $connection = null);

    /**
     * @param Connection|null $connection
     *
     * @return Connection
     */
    protected function connection(Connection $connection = null): Connection
    {
        return isset($connection) ? $connection : $this->index->connection();
    }

}
