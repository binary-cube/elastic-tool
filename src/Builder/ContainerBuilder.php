<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool\Builder;

use Psr\Log\LoggerInterface;
use BinaryCube\ElasticTool\Index;
use BinaryCube\ElasticTool\Config;
use BinaryCube\ElasticTool\Mapping;
use BinaryCube\ElasticTool\Container;
use BinaryCube\ElasticTool\Component;
use BinaryCube\ElasticTool\Connection;
use BinaryCube\ElasticTool\Support\Collection;

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
        'mappings'     => [],
        'indices'     => [],
    ];

    /**
     * @param Config               $config
     * @param LoggerInterface|null $logger
     *
     * @return Container
     */
    public static function create(Config $config, $logger = null): Container
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
     * @param Config $config
     *
     * @return Container
     */
    public function build(Config $config): Container
    {
        $config = Collection::make(static::DEFAULTS)->merge($config->all())->all();

        $container = new Container();

        $this
            ->createConnections($container, $config)
            ->createMappings($container, $config)
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

            $container->connections()->put($entry->id(), $entry);

            $this->logger->debug(\vsprintf('Connection with ID: "%s" has been created', [$entry->id()]));
        }

        unset($connections);

        return $this;
    }

    /**
     * @param Container $container
     * @param array     $config
     *
     * @return $this
     */
    protected function createMappings(Container $container, array $config): self
    {
        $mappings = $config['mappings'];

        $default = [
            'instance'   => null,
            'name'       => null,
            'params'     => [],
            'aliases'    => [],
            'type'       => null,
            'parameters' => [],
        ];

        foreach ($mappings as $id => $mapping) {
            $mapping = Collection::make($default)->merge($mapping)->all();

            if (empty($mapping['name'])) {
                throw new \RuntimeException(\vsprintf('mapping name is empty!', []));
            }

            if (
                ! \class_exists($mapping['instance'])
                || ! \is_a($mapping['instance'], Mapping::class, true)
            ) {
                throw new \LogicException(
                    \vsprintf(
                        "Can't create mapping, '%s' must extend from %s or its child class.",
                        [
                            $mapping['mapping'],
                            Mapping::class,
                        ]
                    )
                );
            }

            $instance = $mapping['instance'];
            $name     = $mapping['name'];
            $params   = $mapping['params'];
            $aliases  = $mapping['aliases'];
            $type     = $mapping['type'];

            $entry = new $instance($id, $name, $params, $aliases, $type, $this->logger);

            $container->mappings()->put($entry->id(), $entry);

            $this->logger->debug(\vsprintf('Mapping with ID: "%s" has been created', [$entry->id()]));
        }//end foreach

        unset($mappings, $default);

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
            'group'      => null,
            'mapping'    => null,
            'config'     => [],
            'connection' => null,
            'params'     => [],
        ];

        foreach ($indices as $id => $index) {
            $index = Collection::make($default)->merge($index)->all();

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
                            $index['instance'],
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
                ! empty($index['mapping'])
                && ! $container->mappings()->has($index['mapping'])
            ) {
                throw new \RuntimeException(
                    \vsprintf(
                        'Could not create index "%s": mapping with id "%s" is not defined!',
                        [
                            $index['name'],
                            $index['mapping'],
                        ]
                    )
                );
            }

            $instance   = $index['instance'];
            $name       = $index['name'];
            $group      = $index['group'];
            $mapping    = $container->mappings()->getIfSet((string) $index['mapping']);
            $config     = $index['config'];
            $connection = $container->connections()->get($index['connection']);
            $params     = (array) $index['params'];

            $entry = new $instance($id, $name, $connection, $mapping, $group, $config, $params, $this->logger);

            $container->indices()->put($entry->id(), $entry);

            $this->logger->debug(\vsprintf('Index with ID: "%s" has been created', [$entry->id()]));
        }//end foreach

        unset($indices, $default);

        return $this;
    }

}
