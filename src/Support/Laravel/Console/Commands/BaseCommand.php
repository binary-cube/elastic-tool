<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool\Support\Laravel\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use BinaryCube\ElasticTool\ElasticTool;
use BinaryCube\ElasticTool\Support\Laravel\Console\Commands\Helpers\HasInputSanitizer;
use BinaryCube\ElasticTool\Support\Laravel\Console\Commands\Helpers\HasOutputCatcherToString;

/**
 * Class BaseCommand
 */
abstract class BaseCommand extends Command
{
    use HasInputSanitizer;
    use HasOutputCatcherToString;

    /**
     * @var ElasticTool
     */
    protected $esTool;

    /**
     * Constructor.
     *
     * @param ElasticTool $esTool
     */
    public function __construct(ElasticTool $esTool)
    {
        $this->esTool = $esTool;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return integer
     */
    public function handle()
    {
        try {
            return $this->handleInternal();
        } catch (\Exception $exception) {
            $this->error('Something went wrong! Check the log for more information.');
            Log::error((string) $exception);
        }

        return 0;
    }

    /**
     * @return integer
     */
    abstract protected function handleInternal();

}
