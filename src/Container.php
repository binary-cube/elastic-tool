<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool;

use BinaryCube\ElasticTool\Collection\IndexRepository;
use BinaryCube\ElasticTool\Collection\MappingRepository;
use BinaryCube\ElasticTool\Collection\ConnectionRepository;

/**
 * Class Container
 */
class Container
{

    /**
     * @var ConnectionRepository
     */
    private $connections;

    /**
     * @var MappingRepository
     */
    private $mappings;

    /**
     * @var IndexRepository
     */
    private $indices;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->connections = new ConnectionRepository();
        $this->mappings    = new MappingRepository();
        $this->indices     = new IndexRepository();
    }

    /**
     * @return ConnectionRepository
     */
    public function connections(): ConnectionRepository
    {
        return $this->connections;
    }

    /**
     * @return MappingRepository
     */
    public function mappings(): MappingRepository
    {
        return $this->mappings;
    }

    /**
     * @return IndexRepository
     */
    public function indices(): IndexRepository
    {
        return $this->indices;
    }

}
