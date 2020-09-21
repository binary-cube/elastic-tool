<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool;

use Elasticsearch\Client;
use Psr\Log\LoggerInterface;
use Elasticsearch\ClientBuilder;

/**
 * Class Connection
 */
class Connection extends Component
{

    /**
     * @const array Default connections parameters
     */
    const DEFAULTS = [
        'hosts'            => [],
        'retries'          => 0,
        'connectionParams' => [],
        'enableLogging'    => false,
    ];

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Client
     */
    protected $client;

    /**
     * Constructor.
     *
     * @param string               $id
     * @param array                $config
     * @param LoggerInterface|null $logger
     */
    public function __construct(string $id, $config = [], $logger = null)
    {
        parent::__construct($id, $logger);

        $this->config = Config::make(static::DEFAULTS)->merge($config);

        $this->refresh();
    }

    /**
     * @return Config
     */
    public function config(): Config
    {
        return $this->config;
    }

    /**
     * @return Client
     */
    public function client(): Client
    {
        return $this->client;
    }

    /**
     * @return $this
     */
    public function refresh(): self
    {
        $this->client = $this->make();

        return $this;
    }

    /**
     * @return Client
     */
    protected function make(): Client
    {
        $config = $this->config->all();

        if (isset($this->logger) && $config['enableLogging']) {
            $config['logger'] = $this->logger;
        }

        return ClientBuilder::fromConfig($config, true);
    }

    /**
     * @return void
     */
    public function __destruct()
    {
        unset($this->config, $this->client);

        parent::__destruct();
    }

}
