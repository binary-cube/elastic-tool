<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool\Builder;

use BinaryCube\ElasticTool\Schema;
use Psr\Log\LoggerInterface;
use BinaryCube\ElasticTool\Index;
use BinaryCube\ElasticTool\Config;
use BinaryCube\ElasticTool\Container;
use BinaryCube\ElasticTool\Component;
use BinaryCube\ElasticTool\Connection;

/**
 * Class ContainerBuilder
 */
class ContainerBuilder extends Component
{

    /**
     * @const array Default parameters.
     */
    const DEFAULTS = [
        'connections' => [],
        'schemas'     => [],
        'indices'     => [],
    ];

    /**
     * @param array                $config
     * @param LoggerInterface|null $logger
     *
     * @return Container
     */
    public static function create(array $config, $logger = null): Container
    {
        $builder = new static($logger);

        return $builder->build($config);
    }

    /**
     * Constructor.
     *
     * @param LoggerInterface|null $logger
     */
    public function __construct($logger = null)
    {
        parent::__construct(null, $logger);
    }

    /**
     * @param array $config
     *
     * @return Container
     */
    public function build(array $config): Container
    {
        $config = Config::make(static::DEFAULTS)->mergeWith($config)->toArray();

        $container = new Container();

        $this
            ->createConnections($container, $config)
            ->createSchemas($container, $config)
            ->createIndices($container, $config);

        return $container;
    }

    /**
     * @param Container $container
     * @param array     $config
     *
     * @return $this
     */
    protected function createConnections(Container $container, array $config): self
    {
        $connections = $config['connections'];

        foreach ($connections as $id => $config) {
            $entry = new Connection($id, $config, $this->logger);

            $container->connections()->set($entry->id(), $entry);

            $this->logger->debug(\vsprintf('Connection with ID: "%s" has been created', [$entry->id()]));
        }

        unset($connections);

        return $this;
    }

    /**
     * @param Container $container
     * @param array     $schema
     *
     * @return $this
     */
    protected function createSchemas(Container $container, array $schema): self
    {
        $schemas = $schema['schemas'];

        $default = [
            'instance'   => null,
            'name'       => null,
            'params'     => [],
            'aliases'    => [],
            'type'       => null,
        ];

        foreach ($schemas as $id => $schema) {
            $schema = Config::make($default)->mergeWith($schema)->toArray();

            if (empty($schema['name'])) {
                throw new \RuntimeException(\vsprintf('Schema name is empty!', []));
            }

            if (
                ! \class_exists($schema['instance'])
                || ! \is_a($schema['instance'], Schema::class, true)
            ) {
                throw new \LogicException(
                    \vsprintf(
                        "Can't create schema, '%s' must extend from %s or its child class.",
                        [
                            \get_class($schema['schema']),
                            Schema::class,
                        ]
                    )
                );
            }

            $instance = $schema['instance'];
            $name     = $schema['name'];
            $params   = $schema['params'];
            $aliases  = $schema['aliases'];
            $type     = $schema['type'];

            $entry = new $instance($id, $name, $params, $aliases, $type, $this->logger);

            $container->schemas()->set($entry->id(), $entry);

            $this->logger->debug(\vsprintf('Schema with ID: "%s" has been created', [$entry->id()]));
        }//end foreach

        unset($schemas, $default);

        return $this;
    }

    /**
     * @param Container $container
     * @param array     $config
     *
     * @return $this
     */
    protected function createIndices(Container $container, array $config): self
    {
        $indices = $config['indices'];

        $default = [
            'instance'   => Index::class,
            'name'       => null,
            'connection' => null,
            'schema'     => null,
            'config'     => [],
        ];

        foreach ($indices as $id => $index) {
            $index = Config::make($default)->mergeWith($index)->toArray();

            if (empty($index['name'])) {
                throw new \RuntimeException(\vsprintf('Index name is empty!', []));
            }

            if (
                ! \class_exists($index['instance'])
                || ! \is_a($index['instance'], Index::class, true)
            ) {
                throw new \LogicException(
                    \vsprintf(
                        "Can't create index, '%s' must extend from %s or its child class.",
                        [
                            \get_class($index['instance']),
                            Index::class,
                        ]
                    )
                );
            }

            if (
                empty($index['connection']) ||
                ! $container->connections()->has($index['connection'])
            ) {
                throw new \RuntimeException(
                    \vsprintf(
                        'Could not create index "%s": connection with id "%s" is not defined!',
                        [
                            $index['name'],
                            $index['connection'],
                        ]
                    )
                );
            }

            if (
                ! empty($index['schema'])
                && ! $container->schemas()->has($index['schema'])
            ) {
                throw new \RuntimeException(
                    \vsprintf(
                        'Could not create index "%s": schema with id "%s" is not defined!',
                        [
                            $index['name'],
                            $index['schema'],
                        ]
                    )
                );
            }

            $instance   = $index['instance'];
            $name       = $index['name'];
            $connection = $container->connections()->get($index['connection']);
            $params     = $index['config'];
            $schema     = $container->schemas()->getIfSet($index['schema']);

            $entry = new $instance($id, $name, $connection, $schema, $params, $this->logger);

            $container->indices()->set($entry->id(), $entry);

            $this->logger->debug(\vsprintf('Index with ID: "%s" has been created', [$entry->id()]));
        }//end foreach

        unset($indices, $default);

        return $this;
    }

}
