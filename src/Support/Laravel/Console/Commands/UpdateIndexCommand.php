<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool\Support\Laravel\Console\Commands;

use BinaryCube\ElasticTool\Index;
use BinaryCube\ElasticTool\Support\Laravel\Console\Commands\Helpers\ProxyIndexCommand;

/**
 * Class UpdateIndexCommand
 */
class UpdateIndexCommand extends BaseIndexCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '
                            elastic-tool:update-index
                            {--i|include= : Include index or mapping in the process of creating/updating index. Default is index}
                            {--f|force= : Close the index before applying any changes and at the end Open the index}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Index';

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
        $force   = \filter_var($this->input->getOption('force'), FILTER_VALIDATE_BOOLEAN);
        $include = empty($include) ? [self::INCLUDE_INDEX] : $include;

        if ($force) {
            $proxy->close($row);
        }

        if (\in_array(self::INCLUDE_INDEX, $include)) {
            $proxy->update($row);
        }

        if (\in_array(self::INCLUDE_MAPPING, $include)) {
            $proxy->updateMapping($row);
        }

        if ($force) {
            $proxy->open($row);
        }
    }

}
