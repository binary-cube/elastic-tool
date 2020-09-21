<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool\Support;

/**
 * Trait ParameterAwareTrait
 */
trait ParameterAwareTrait
{

    /**
     * @var Collection
     */
    private $parameters;

    /**
     * @return Collection
     */
    public function parameters()
    {
        if (! isset($this->parameters)) {
            $this->parameters = new Collection();
        }

        return $this->parameters;
    }

}
