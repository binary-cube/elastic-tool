<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool;

/**
 * Class DataMapper
 *
 * A simple tool that takes as input an array and output an array following the given Schema(@see Schema) structure.
 *
 * WARNING: Properties that are not found in schema they will be dropped.
 */
class DataMapper
{

    /**
     * @var Schema
     */
    protected $schema;

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * @param Schema $schema
     *
     * @return static
     */
    public static function usingSchema(Schema $schema)
    {
        return new static($schema);
    }

    /**
     * Constructor.
     *
     * @param Schema $schema
     */
    public function __construct(Schema $schema)
    {
        $this->schema = $schema;
    }

    /**
     * @return $this
     */
    public function refresh(): self
    {
        $this->prepareProperties(true);

        return $this;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function map(array $data): array
    {
        $this->prepareProperties();

        return self::buildMap($data, $this->properties);
    }

    /**
     * @param bool $refresh
     *
     * @return $this
     */
    protected function prepareProperties($refresh = false): self
    {
        if ($refresh || empty($this->properties)) {
            $this->properties = $this->buildProperties();
        }

        return $this;
    }

    /**
     * @return array
     */
    protected function buildProperties(): array
    {
        $properties = self::propertiesToFlat($this->schema->getProperties());
        $aliases    = self::propertyAliasesToFlat($this->schema->getAliases());

        // Bind aliases to main property.
        foreach ($aliases as $alias => $key) {
            if (! isset($properties[$key])) {
                continue;
            }

            $properties[$alias] = $properties[$key];
        }

        return [
            'flat' => $properties,
        ];
    }

    /**
     * Recursive function that iterate over mapping properties and flat them.
     * ex:
     * {
     *      id: {...},
     *      stock: {
     *          id: {...},
     *          available: {...}
     *      }
     * }
     * Become:
     * {
     *      id: {
     *          key: original-key => id,
     *          type: ...
     *      },
     *      stock.id: {
     *          key: original-key => id,
     *          type: ...
     *      }
     *      stock.available: {
     *          key: original-key => available,
     *          type: ...
     *      }
     * }
     *
     * @param array       $properties
     * @param null|string $parent
     *
     * @return array
     */
    private static function propertiesToFlat(array $properties, $parent = null): array
    {
        $result = [];

        if (! isset($parent)) {
            $result['root'] =
                [
                    'map' => \array_flip(\array_keys(\array_filter(
                        $properties,
                        function ($item) {
                            if (isset($item['type']) && $item['type'] === SchemaFieldType::NESTED) {
                                return false;
                            }

                            return true;
                        }
                    ))),
                ];
        }

        foreach ($properties as $key => $value) {
            $pKey = ! isset($parent) ? $key : ($parent . '.' . $key);
            $type = isset($value['type']) ? $value['type'] : null;

            $result[$pKey] = [
                'key'   => $key,
                'type'  => $type,
                'map'   => ($type === SchemaFieldType::NESTED) ? \array_flip(\array_keys($value['properties'])) : [],
            ];

            if ($type === SchemaFieldType::NESTED) {
                $result = \array_merge($result, self::propertiesToFlat($value['properties'], $pKey));
            }
        }

        return $result;
    }

    /**
     * @param array       $aliases
     * @param null|string $parent
     *
     * @return array
     */
    private static function propertyAliasesToFlat(array $aliases, $parent = null): array
    {
        $result = [];

        foreach ($aliases as $key => $value) {
            $pKey      = ! isset($parent) ? $key : ($parent . '.' . $key);
            $pValueKey = (\is_array($value) ? $pKey : (! isset($parent) ? $value : ($parent . '.' . $value)));

            if (\is_array($value)) {
                $result = \array_merge($result, self::propertyAliasesToFlat(\array_flip($value), $pKey));
                continue;
            }

            $result[$pKey] = $pValueKey;
        }

        return $result;
    }

    /**
     * @param array       $data
     * @param array       $schema
     * @param null|string $parent
     *
     * @return array
     */
    protected static function buildMap(array $data, array $schema, $parent = null)
    {
        $result = [];

        foreach ($data as $key => $value) {
            $pKey = ! isset($parent) ? $key : ($parent . '.' . $key);

            if (! isset($schema['flat'][$pKey])) {
                continue;
            }

            $originalKey = $schema['flat'][$pKey]['key'];
            $type        = $schema['flat'][$pKey]['type'];

            // Cast to Type.
            switch ($type) {
                case SchemaFieldType::TEXT:
                case SchemaFieldType::KEYWORD:
                    $value = (string) $value;
                    break;

                case SchemaFieldType::SHORT:
                case SchemaFieldType::INTEGER:
                    $value = (int) $value;
                    break;

                case SchemaFieldType::DOUBLE:
                    $value = (double) $value;
                    break;

                case SchemaFieldType::FLOAT:
                    $value = (float) $value;
                    break;

                case SchemaFieldType::BOOLEAN:
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    break;

                case SchemaFieldType::DATE:
                    if (empty($value) == true || $value == '0000-00-00') {
                        $value = null;
                    }
                    break;

                case SchemaFieldType::NESTED:
                    // Check if $value is associative.
                    if (\is_array(current($value))) {
                        foreach ($value as $k => $v) {
                            $value[$k] = self::buildMap($v, $schema, $pKey);
                        }
                    } else {
                        $value = self::buildMap($value, $schema, $pKey);
                    }
                    break;
            }//end switch

            $result[$originalKey] = $value;
        }//end foreach

        // Keep the same order as was defined in schema.
        $_p     = isset($parent) ? $parent : 'root';
        $result = \array_merge(\array_intersect_key(($schema['flat'][$_p]['map']), $result), $result);

        return $result;
    }

}
