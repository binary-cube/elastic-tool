<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool;

/**
 * Class DataMapper
 *
 * A simple tool that takes as input an array and output an array following the given Mapping(@see Mapping) structure.
 *
 * WARNING: Properties that are not found in Mapping they will be dropped.
 */
class DataMapper
{

    /**
     * @var Mapping
     */
    protected $mapping;

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * @param Mapping $mapping
     *
     * @return static
     */
    public static function usingMapping(Mapping $mapping)
    {
        return new static($mapping);
    }

    /**
     * Constructor.
     *
     * @param Mapping $mapping
     */
    public function __construct(Mapping $mapping)
    {
        $this->mapping = $mapping;
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
        $properties = self::propertiesToFlat($this->mapping->getProperties());
        $aliases    = self::propertyAliasesToFlat($this->mapping->getAliases());

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

        $innerTypes = [
            MappingFieldType::NESTED,
            MappingFieldType::OBJECT,
        ];

        if (! isset($parent)) {
            $result['root'] =
                [
                    'map' => \array_flip(\array_keys(\array_filter(
                        $properties,
                        function ($item) use ($innerTypes) {
                            return ! (
                                isset($item['type'])
                                || (isset($item['properties']) && (\is_array($item['properties'])))
                            );
                        }
                    ))),
                ];
        }

        foreach ($properties as $key => $value) {
            $pKey = ! isset($parent) ? $key : ($parent . '.' . $key);
            $type = isset($value['type']) ? $value['type'] : null;

            if (
                ! isset($type)
                && isset($value['properties'])
                && (\is_array($value['properties']))
            ) {
                $type = MappingFieldType::OBJECT;
            }

            $result[$pKey] = [
                'key'   => $key,
                'type'  => $type,
                'map'   => \in_array($type, $innerTypes) ? \array_flip(\array_keys($value['properties'])) : [],
            ];

            if (\in_array($type, $innerTypes)) {
                $result = \array_merge($result, self::propertiesToFlat($value['properties'], $pKey));
            }
        }//end foreach

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
     * @param array       $mapping
     * @param null|string $parent
     *
     * @return array
     */
    protected static function buildMap(array $data, array $mapping, $parent = null)
    {
        $result = [];

        foreach ($data as $key => $value) {
            $pKey = ! isset($parent) ? $key : ($parent . '.' . $key);

            if (! isset($mapping['flat'][$pKey])) {
                continue;
            }

            $originalKey = $mapping['flat'][$pKey]['key'];
            $type        = $mapping['flat'][$pKey]['type'];

            // Cast to Type.
            switch ($type) {
                case MappingFieldType::TEXT:
                case MappingFieldType::KEYWORD:
                    if (\is_array($value)) {
                        foreach ($value as $k => $v) {
                            $value[$k] = (string) $v;
                        }
                    } else {
                        $value = (string) $value;
                    }
                    break;

                case MappingFieldType::SHORT:
                case MappingFieldType::INTEGER:
                    $value = (int) $value;
                    break;

                case MappingFieldType::LONG:
                case MappingFieldType::DOUBLE:
                    $value = (double) $value;
                    break;

                case MappingFieldType::SCALED_FLOAT:
                case MappingFieldType::HALF_FLOAT:
                case MappingFieldType::FLOAT:
                    $value = (float) $value;
                    break;

                case MappingFieldType::BOOLEAN:
                    $value = \filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    break;

                case MappingFieldType::DATE:
                    if (empty($value) == true || $value == '0000-00-00') {
                        $value = null;
                    }
                    break;

                case MappingFieldType::OBJECT:
                case MappingFieldType::NESTED:
                    // Check if $value is associative.
                    if (\is_array(\current($value))) {
                        foreach ($value as $k => $v) {
                            $nestedPKey = $pKey;

                            if (isset($mapping['flat'][$pKey . '.' . $k])) {
                                $nestedPKey = ($pKey . '.' . $k);
                            }

                            $value[$k] = self::buildMap($v, $mapping, $nestedPKey);
                        }
                    } else {
                        $value = self::buildMap($value, $mapping, $pKey);
                    }
                    break;
            }//end switch

            $result[$originalKey] = $value;
        }//end foreach

        // Keep the same order as was defined in mapping.
        $_p     = isset($parent) ? $parent : 'root';
        $result = \array_merge(\array_intersect_key(($mapping['flat'][$_p]['map']), $result), $result);

        return $result;
    }

}
