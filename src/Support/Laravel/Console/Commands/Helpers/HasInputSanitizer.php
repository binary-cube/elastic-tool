<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool\Support\Laravel\Console\Commands\Helpers;

/**
 * Trait HasInputSanitizer
 */
trait HasInputSanitizer
{

    /**
     * @param mixed  $values
     * @param string $delimiter
     * @param bool   $excludeEmpty
     *
     * @return mixed
     */
    public function sanitize($values, $delimiter = ',', $excludeEmpty = true)
    {
        if (\is_string($values)) {
            $values = \explode($delimiter, $values);
        }

        $values = \array_map('trim', (array) $values);

        if ($excludeEmpty) {
            $values = \array_filter($values);
        }

        return $values;
    }

}
