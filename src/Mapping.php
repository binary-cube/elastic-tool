<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool;

use Psr\Log\LoggerInterface;

/**
 * Class Mapping
 */
abstract class Mapping extends Component
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type = null;

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @var array
     */
    protected $aliases = [];

    /**
     * Constructor.
     *
     * @param string               $id
     * @param string               $name
     * @param array                $params
     * @param array                $aliases
     * @param null|string          $type
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        string $id,
        string $name,
        $params = [],
        $aliases = [],
        $type = null,
        $logger = null
    ) {
        parent::__construct($id, $logger);

        $this->name    = $name;
        $this->params  = (array) $params;
        $this->aliases = (array) $aliases;
        $this->type    = $type;
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
    public function type()
    {
        return $this->type;
    }

    /**
     * Possible options:
     *      _uid
     *      _id
     *      _type
     *      _source
     *      _analyzer
     *      _routing
     *      _index
     *      _size
     *      properties
     *
     * @param string       $key
     * @param string|array $value
     *
     * @return $this
     */
    public function setParam(string $key, $value): self
    {
        $this->params[$key] = $value;

        return $this;
    }

    /**
     * @param $key
     *
     * @return mixed|null
     */
    public function getParam(string $key)
    {
        return ($this->params[$key] ?? null);
    }

    /**
     * Sets the mapping properties.
     *
     * @param array $properties Properties
     *
     * @return $this
     */
    public function setProperties(array $properties): self
    {
        $this->setParam('properties', $properties);

        return $this;
    }

    /**
     * Gets the mapping properties.
     *
     * @return array
     */
    public function getProperties(): array
    {
        return ($this->getParam('properties') ?? []);
    }

    /**
     * @param array $aliases
     *
     * @return $this
     */
    public function setAliases(array $aliases): self
    {
        $this->aliases = [];

        foreach ($aliases as $property => $alias) {
            if (! \is_string($property)) {
                continue;
            }

            $this->aliases[$property] = $alias;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * Sets the mapping _meta.
     *
     * @param array $meta metadata
     *
     * @return $this
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/master/mapping-meta-field.html
     */
    public function setMeta(array $meta): self
    {
        $this->setParam('_meta', $meta);

        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getMeta()
    {
        return $this->getParam('_meta');
    }

    /**
     * Sets source values.
     *
     * To disable source, argument is
     * ['enabled' => false]
     *
     * @param array $source Source array
     *
     * @return $this
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping-source-field.html
     */
    public function setSource(array $source): self
    {
        $this->setParam('_source', $source);

        return $this;
    }

    /**
     * Disables the source in the index.
     *
     * Param can be set to true to enable again
     *
     * @param bool $enabled OPTIONAL (default = false)
     *
     * @return $this
     */
    public function disableSource($enabled = false): self
    {
        $this->setSource(['enabled' => $enabled]);

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        if (empty($this->type)) {
            return $this->params;
        }

        return [$this->type => $this->params];
    }

}
