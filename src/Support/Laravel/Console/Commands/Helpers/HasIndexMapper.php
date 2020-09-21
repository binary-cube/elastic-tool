<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool\Support\Laravel\Console\Commands\Helpers;

use BinaryCube\ElasticTool\Collection\IndexCollection;

/**
 * Trait HasIndexMapper
 */
trait HasIndexMapper
{

    /**
     * @param array $source
     * @param null  $group
     *
     * @return array
     */
    protected function mapIndices(array $source, $group = null): array
    {
        $result = [
            'ok'        => [],
            'not-found' => [],
        ];

        /**
         * @var IndexCollection $indices
         */
        $indices = $this->esTool->container()->indices();
        $indices = empty($group) ? $indices : $indices->inGroup($group);

        $source = \in_array('all', $source) ? $indices->ids() : $source;

        foreach ($source as $id) {
            if (! $indices->has($id)) {
                $result['not-found'][$id] = \vsprintf(
                    'Index "%s" not found%s.',
                    [
                        $id,
                        empty($group) ? '' : ' in group ' . $group,
                    ]
                );
                continue;
            }

            $element = $indices->get($id);

            $result['ok'][$element->id()] = [
                'element' => $element,

                'info' => [
                    'group'   => ($element->group() ?? '-'),
                    'id'      => $element->id(),
                    'name'    => $element->name(),
                    'mapping' => $element->isHavingMappingSet() ? $element->mapping()->id() : '-',
                ],
            ];
        }//end foreach

        return $result;
    }

}
