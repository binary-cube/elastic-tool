<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool;

use Psr\Log\LoggerInterface;
use BinaryCube\ElasticTool\Builder\ContainerBuilder;

/**
 * Class ElasticTool
 */
class ElasticTool extends Component
{

    /**
     * @var array
     */
    protected $config;

    /**
     * @var Container
     */
    protected $container;

    /**
     * Constructor.
     *
     * @param array                $config
     * @param LoggerInterface|null $logger
     */
    public function __construct($config = [], LoggerInterface $logger = null)
    {
        parent::__construct(null, $logger);

        $this->config = $config;

        $this->logger->debug(\vsprintf('Instance of "%s" has been created', [self::class]));
    }

    /**
     * @return Container
     */
    public function container(): Container
    {
        if (empty($this->container)) {
            $this->container = ContainerBuilder::create($this->config, $this->logger);
        }

        return $this->container;
    }

}
