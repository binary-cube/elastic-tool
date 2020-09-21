<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool;

use Psr\Log\LoggerInterface;
use BinaryCube\ElasticTool\Endpoint\CatProxyEndpoint;
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
        'aliases' => [],
    ];

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $group;

    /**
     * @var Mapping
     */
    protected $mapping;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * Constructor.
     *
     * @param string               $id
     * @param string               $name
     * @param Connection           $connection
     * @param Mapping|null         $mapping
     * @param string|null          $group
     * @param array                $config
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        string $id,
        string $name,
        Connection $connection,
        Mapping $mapping = null,
        string $group = null,
        $config = [],
        $logger = null
    ) {
        parent::__construct($id, $logger);

        $this->name       = $name;
        $this->group      = $group;
        $this->mapping    = $mapping;
        $this->config     = Config::make(static::DEFAULTS)->merge($config);
        $this->connection = $connection;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function group()
    {
        return $this->group;
    }

    /**
     * @return Mapping|null
     */
    public function mapping()
    {
        return $this->mapping;
    }

    /**
     * @return Config
     */
    public function config(): Config
    {
        return $this->config;
    }

    /**
     * @return Connection
     */
    public function connection(): Connection
    {
        return $this->connection;
    }

    /**
     * @param Mapping $mapping
     *
     * @return $this
     */
    public function withMapping(Mapping $mapping): self
    {
        $this->mapping = $mapping;

        return $this;
    }

    /**
     * @return bool
     */
    public function isHavingMappingSet(): bool
    {
        return isset($this->mapping);
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
                ->merge(['index' => $this->name])
                ->all()
        );

        $status = (
            (new CatProxyEndpoint($this, $this->logger))
                ->viaMethod('indices')
                ->execute($params, $connection)
        );

        return (isset($status[0]) && $status[0]['status'] === 'open');
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
        $status = (
            (new IndexProxyEndpoint($this, $this->logger))
                ->viaMethod('stats')
                ->execute($params, $connection)
        );

        $cat = (
            (new CatProxyEndpoint($this, $this->logger))
                ->viaMethod('indices')
                ->execute(
                    Config::make((array) $params)->merge(['index' => $this->name])->all(),
                    $connection
                )
        );

        return (
            Config::make([])
                ->merge(['summary' => $cat[0]])
                ->merge(['detailed' => $status['indices'][$this->name]])
                ->all()
        );
    }

    /**
     * Create only the index settings without mapping or aliases.
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
            'body' => $this->sanitizeData([
                'settings' => (
                    Config::make([])
                        ->merge((array) $this->config->get('main', []))
                        ->merge((array) $this->config->get('create', []))
                        ->merge((array) $params)
                        ->all()
                ),
            ]),
        ];

        return (
            (new IndexProxyEndpoint($this, $this->logger))
                ->viaMethod('create')
                ->execute($params, $connection)
        );
    }

    /**
     * Update only the index settings without mapping or aliases.
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
            'body' => $this->sanitizeData([
                'settings' => (
                    Config::make([])
                        ->merge((array) $this->config->get('main', []))
                        ->merge((array) $this->config->get('update', []))
                        ->merge((array) $params)
                        ->all()
                ),
            ]),
        ];

        if (! isset($params['body']['settings'])) {
            return false;
        }

        return (
            (new IndexProxyEndpoint($this, $this->logger))
                ->viaMethod('putSettings')
                ->execute($params, $connection)
        );
    }

    /**
     * Update only the index mapping.
     *
     * @param null|array      $params
     * @param Connection|null $connection
     *
     * @return mixed Return false in case there is no mapping attached.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/master/indices-put-mapping.html
     */
    public function updateMapping($params = null, Connection $connection = null)
    {
        if (! $this->isHavingMappingSet()) {
            return false;
        }

        $params = [
            'body' => (
                Config::make((array) $params)
                    ->merge($this->mapping->toArray())
                    ->all()
            ),
        ];

        if (! empty($this->mapping->type())) {
            $params['type'] = $this->mapping->type();
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
     * @param array $config
     *
     * @return array
     */
    protected function sanitizeData(array $config): array
    {
        if (isset($config['settings']) && empty($config['settings'])) {
            unset($config['settings']);
        }

        if (isset($config['mappings']) && empty($config['mappings'])) {
            unset($config['mappings']);
        }

        if (isset($config['aliases']) && empty($config['aliases'])) {
            unset($config['aliases']);
        }

        return $config;
    }

    /**
     * @param bool $includeMapping
     *
     * @return array
     */
    public function toArray($includeMapping = true): array
    {
        $data['settings'] = (
            Config::make([])
                ->merge((array) $this->config->get('main', []))
                ->merge((array) $this->config->get('create', []))
                ->merge((array) $this->config->get('update', []))
                ->all()
        );

        if ($includeMapping && $this->isHavingMappingSet()) {
            $data['mappings'] = $this->mapping->toArray();
        }

        $data = $this->sanitizeData($data);

        return $data;
    }

    /**
     * @return void
     */
    public function __destruct()
    {
        unset(
            $this->name,
            $this->group,
            $this->mapping,
            $this->config,
            $this->connection
        );

        parent::__destruct();
    }

}
