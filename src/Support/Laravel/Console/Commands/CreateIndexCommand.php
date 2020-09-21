<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool\Support\Laravel\Console\Commands;

use BinaryCube\ElasticTool\Index;
use BinaryCube\ElasticTool\Support\Laravel\Console\Commands\Helpers\ProxyIndexCommand;

/**
 * Class CreateIndexCommand
 */
class CreateIndexCommand extends BaseIndexCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '
                            elastic-tool:create-index
                            {--i|include= : Include index or mapping in the process of creating/updating index. Default is index}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Index';

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
        $proxy   = (new ProxyIndexCommand($index, $callback));
        $include = \array_merge([self::INCLUDE_INDEX], $include);

        if (\in_array(self::INCLUDE_INDEX, $include)) {
            $proxy->create($row);
        }

        if (\in_array(self::INCLUDE_MAPPING, $include)) {
            $proxy->updateMapping($row);
        }
    }

}
