<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool\Support\Laravel\Console\Commands;

use BinaryCube\ElasticTool\Index;
use BinaryCube\ElasticTool\Support\Laravel\Console\Commands\Helpers\ProxyIndexCommand;

/**
 * Class RefreshIndexCommand
 */
class RefreshIndexCommand extends BaseIndexCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic-tool:refresh-index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh Index';

    /**
     * @param array         $row
     * @param Index         $index
     * @param array         $include
     * @param callable|null $callback
     *
     * @return void
     */
    protected function process(array &$row, Index $index, array $include = [], callable $callback = null): void
    {
        (new ProxyIndexCommand($index, $callback))->refresh($row);
    }

}
