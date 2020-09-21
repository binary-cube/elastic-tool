<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool\Support\Laravel\Console\Commands\Helpers;

use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * Trait HasIndexListing
 */
trait HasOutputCatcherToString
{

    /**
     * @param callable $callback The callback which will be executed and catch any output
     *                           The signature of callback must be `function ($input, $output) {...}`.
     *
     * @return string
     */
    public function catchOutputToString(callable $callback): string
    {
        $current = $this->output;
        $stream  = \fopen('php://memory', 'r+');

        $out = new StreamOutput(
            $stream,
            $current->getVerbosity(),
            $current->isDecorated(),
            $current->getFormatter()
        );

        $this->setOutput(new OutputStyle($this->input, $out));

        \call_user_func_array($callback, [$this->input, $out]);

        \rewind($stream);

        $content = \stream_get_contents($stream);

        \fclose($stream);

        $this->setOutput($current);

        return $content;
    }

}
