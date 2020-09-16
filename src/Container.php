<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool;

use BinaryCube\ElasticTool\Collection\IndexList;
use BinaryCube\ElasticTool\Collection\SchemaList;
use BinaryCube\ElasticTool\Collection\ConnectionList;

/**
 * Class Container
 */
class Container
{

    /**
     * @var ConnectionList
     */
    private $connections;

    /**
     * @var SchemaList
     */
    private $schemas;

    /**
     * @var IndexList
     */
    private $indices;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->connections = new ConnectionList();
        $this->schemas     = new SchemaList();
        $this->indices     = new IndexList();
    }

    /**
     * @return ConnectionList
     */
    public function connections(): ConnectionList
    {
        return $this->connections;
    }

    /**
     * @return SchemaList
     */
    public function schemas(): SchemaList
    {
        return $this->schemas;
    }

    /**
     * @return IndexList
     */
    public function indices(): IndexList
    {
        return $this->indices;
    }

}
