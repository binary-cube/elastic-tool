<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool;

use BinaryCube\ElasticTool\Endpoint\CatProxyEndpoint;
use Psr\Log\LoggerInterface;
use BinaryCube\ElasticTool\Endpoint\IndexProxyEndpoint;

/**
 * Class Index
 */
class Index extends Component
{

    /**
     * @const array Default Index parameters
     */
    const DEFAULTS = [
        'main'    => [],
        'create'  => [],
        'update'  => [],
    ];

    /**
     * @var string
     */
    protected $name;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var Schema
     */
    protected $schema;

    /**
     * @var array
     */
    protected $config;

    /**
     * Constructor.
     *
     * @param string               $id
     * @param string               $name
     * @param Connection           $connection
     * @param Schema|null          $schema
     * @param array                $config
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        string $id,
        string $name,
        Connection $connection,
        Schema $schema = null,
        $config = [],
        $logger = null
    ) {
        parent::__construct($id, $logger);

        $this->name       = $name;
        $this->connection = $connection;
        $this->schema     = $schema;
        $this->config     = Config::make(static::DEFAULTS)->mergeWith($config)->toArray();
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return Connection
     */
    public function connection(): Connection
    {
        return $this->connection;
    }

    /**
     * @return array
     */
    public function config(): array
    {
        return $this->config;
    }

    /**
     * @return Schema|null
     */
    public function schema()
    {
        return $this->schema;
    }

    /**
     * @param Schema $schema
     *
     * @return $this
     */
    public function withSchema(Schema $schema): self
    {
        $this->schema = $schema;

        return $this;
    }

    /**
     * @return bool
     */
    public function isHavingSchema(): bool
    {
        return isset($this->schema);
    }

    /**
     * @param null|array      $params
     * @param Connection|null $connection
     *
     * @return boolean|mixed
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/master/indices-exists.html
     */
    public function exists($params = null, Connection $connection = null)
    {
        return (
            (new IndexProxyEndpoint($this, $this->logger))
                ->viaMethod('exists')
                ->execute($params, $connection)
        );
    }

    /**
     * @param null            $params
     * @param Connection|null $connection
     *
     * @return bool
     */
    public function isOpen($params = null, Connection $connection = null): bool
    {
        $params = (
            Config::make((array) $params)
                ->mergeWith(['index' => $this->name])
                ->toArray()
        );

        $status = (
            (new CatProxyEndpoint($this, $this->logger))
                ->viaMethod('indices')
                ->execute($params, $connection)
        );

        if (isset($status[0]) && $status[0]['status'] !== 'open') {
            return false;
        }

        unset($status);

        return true;
    }

    /**
     * @param null|array      $params
     * @param Connection|null $connection
     *
     * @return mixed
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/master/indices-open-close.html
     */
    public function open($params = null, Connection $connection = null)
    {
        return (
            (new IndexProxyEndpoint($this, $this->logger))
                ->viaMethod('open')
                ->execute($params, $connection)
        );
    }

    /**
     * @param null|array      $params
     * @param Connection|null $connection
     *
     * @return mixed
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/master/indices-open-close.html
     */
    public function close($params = null, Connection $connection = null)
    {
        return (
            (new IndexProxyEndpoint($this, $this->logger))
                ->viaMethod('close')
                ->execute($params, $connection)
        );
    }

    /**
     * @param null|array      $params
     * @param Connection|null $connection
     *
     * @return mixed
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/master/indices-refresh.html
     */
    public function refresh($params = null, Connection $connection = null)
    {
        return (
            (new IndexProxyEndpoint($this, $this->logger))
                ->viaMethod('refresh')
                ->execute($params, $connection)
        );
    }

    /**
     * @param null|array      $params
     * @param Connection|null $connection
     *
     * @return mixed
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/master/indices-stats.html
     */
    public function stats($params = null, Connection $connection = null)
    {
        $params = (
            Config::make((array) $params)
                ->mergeWith(['index' => $this->name])
                ->toArray()
        );

        $status = (
            (new CatProxyEndpoint($this, $this->logger))
                ->viaMethod('indices')
                ->execute($params, $connection)
        );

        return $status[0];
    }

    /**
     * Create only the index settings without schema or aliases.
     *
     * @param null|array      $params
     * @param Connection|null $connection
     *
     * @return mixed
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-create-index.html
     */
    public function create($params = null, Connection $connection = null)
    {
        $params = [
            'body' => [
                'settings' => (
                    Config::make()
                        ->mergeWith((array) $this->config['main'])
                        ->mergeWith((array) $this->config['create'])
                        ->mergeWith((array) $params)
                        ->toArray()
                ),
            ],
        ];

        return (
            (new IndexProxyEndpoint($this, $this->logger))
                ->viaMethod('create')
                ->execute($params, $connection)
        );
    }

    /**
     * Update only the index settings without schema or aliases.
     *
     * @param null|array      $params
     * @param Connection|null $connection
     *
     * @return mixed
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/master/indices-update-settings.html
     */
    public function update($params = null, Connection $connection = null)
    {
        $params = [
            'body' => [
                'settings' => (
                    Config::make()
                        ->mergeWith((array) $this->config['main'])
                        ->mergeWith((array) $this->config['update'])
                        ->mergeWith((array) $params)
                        ->toArray()
                ),
            ],
        ];

        return (
            (new IndexProxyEndpoint($this, $this->logger))
                ->viaMethod('putSettings')
                ->execute($params, $connection)
        );
    }

    /**
     * Update only the index schema / mapping.
     *
     * @param null|array      $params
     * @param Connection|null $connection
     *
     * @return mixed Return false in case there is no schema attached.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/master/indices-put-mapping.html
     */
    public function updateSchema($params = null, Connection $connection = null)
    {
        if (! $this->isHavingSchema()) {
            return false;
        }

        $params = [
            'body' => (
                Config::make()
                    ->mergeWith((array) $params)
                    ->mergeWith($this->schema->toArray())
                    ->toArray()
            ),
        ];

        if (! empty($this->schema->type())) {
            $params['type'] = $this->schema->type();
        }

        return (
            (new IndexProxyEndpoint($this, $this->logger))
                ->viaMethod('putMapping')
                ->execute($params, $connection)
        );
    }

    /**
     * @param null|array      $params
     * @param Connection|null $connection
     *
     * @return mixed
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/master/indices-delete-index.html
     */
    public function delete($params = null, Connection $connection = null)
    {
        return (
        (new IndexProxyEndpoint($this, $this->logger))
            ->viaMethod('delete')
            ->execute($params, $connection)
        );
    }

    /**
     * @param null|bool       $params
     * @param Connection|null $connection
     *
     * @return mixed
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/master/indices-delete-index.html
     */
    public function readonly($params = false, Connection $connection = null)
    {
        $state = \filter_var($params, FILTER_VALIDATE_BOOLEAN);

        $params = [
            'body' => [
                'index.blocks.read_only_allow_delete' => (! $state) ? null : $state,
            ],
        ];

        return (
            (new IndexProxyEndpoint($this, $this->logger))
                ->viaMethod('putSettings')
                ->execute($params, $connection)
        );
    }

    /**
     * @return void
     */
    public function __destruct()
    {
        unset(
            $this->name,
            $this->connection,
            $this->schema,
            $this->config
        );

        parent::__destruct();
    }

}
