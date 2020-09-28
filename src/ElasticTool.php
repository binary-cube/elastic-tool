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
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array $config
     *
     * @return $this
     */
    public function setConfig(array $config): self
    {
        $this->config    = $config;
        $this->container = null;

        return $this;
    }

    /**
     * @param array $config
     *
     * @return $this
     */
    public function mergeWithConfig(array $config): self
    {
        $this->config    = Config::make($this->config)->merge($config)->all();
        $this->container = null;

        return $this;
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
