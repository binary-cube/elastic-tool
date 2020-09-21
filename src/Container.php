<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool;

use BinaryCube\ElasticTool\Collection\IndexCollection;
use BinaryCube\ElasticTool\Collection\MappingCollection;
use BinaryCube\ElasticTool\Collection\ConnectionCollection;

/**
 * Class Container
 */
class Container
{

    /**
     * @var ConnectionCollection
     */
    private $connections;

    /**
     * @var MappingCollection
     */
    private $mappings;

    /**
     * @var IndexCollection
     */
    private $indices;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->connections = new ConnectionCollection();
        $this->mappings    = new MappingCollection();
        $this->indices     = new IndexCollection();
    }

    /**
     * @return ConnectionCollection
     */
    public function connections(): ConnectionCollection
    {
        return $this->connections;
    }

    /**
     * @return MappingCollection
     */
    public function mappings(): MappingCollection
    {
        return $this->mappings;
    }

    /**
     * @return IndexCollection
     */
    public function indices(): IndexCollection
    {
        return $this->indices;
    }

}
