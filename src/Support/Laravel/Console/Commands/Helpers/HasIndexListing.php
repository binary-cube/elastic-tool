<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool\Support\Laravel\Console\Commands\Helpers;

use BinaryCube\ElasticTool\Index;
use Symfony\Component\Console\Helper\TableSeparator;

/**
 * Trait HasIndexListing
 */
trait HasIndexListing
{

    /**
     * @param Index[] $indices
     *
     * @return void
     */
    protected function listIndices(array $indices)
    {
        $rows  = [];
        $count = \count($indices);

        for ($i = 0; $i < $count; $i++) {
            $rows[] = $indices[$i]['info'];

            if ($i < ($count - 1)) {
                $rows[] = new TableSeparator();
            }
        }

        $this->table(['GROUP', 'ID', 'NAME', 'MAPPING ID'], $rows, 'box');
    }

}
