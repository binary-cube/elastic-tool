<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool\Support\Laravel;

use Psr\Log\LoggerInterface;
use Illuminate\Foundation\Application;
use BinaryCube\ElasticTool\ElasticTool;
use BinaryCube\ElasticTool\Support\Laravel\Console\Commands\ListCommand;
use BinaryCube\ElasticTool\Support\Laravel\Console\Commands\StatsIndexCommand;
use BinaryCube\ElasticTool\Support\Laravel\Console\Commands\OpenIndexCommand;
use BinaryCube\ElasticTool\Support\Laravel\Console\Commands\CloseIndexCommand;
use BinaryCube\ElasticTool\Support\Laravel\Console\Commands\DeleteIndexCommand;
use BinaryCube\ElasticTool\Support\Laravel\Console\Commands\CreateIndexCommand;
use BinaryCube\ElasticTool\Support\Laravel\Console\Commands\UpdateIndexCommand;
use BinaryCube\ElasticTool\Support\Laravel\Console\Commands\RefreshIndexCommand;

/**
 * Class ElasticToolServiceProvider
 */
class ElasticToolServiceProvider extends \Illuminate\Support\ServiceProvider
{

    /**
     * @return void
     */
    public function register()
    {
        $this->publishConfig();
    }

    /**
     * @return void
     */
    public function boot()
    {
        $this->prepareApp();
        $this->prepareCommands();
    }

    /**
     * @return void
     */
    protected function publishConfig()
    {
        $this
            ->publishes(
                [
                    $this->getConfigFile() => config_path('elastic_tool.php'),
                ]
            );
    }

    /**
     * @return void
     */
    protected function prepareApp()
    {
        $config = config('elastic_tool', []);

        if (! \is_array($config)) {
            throw new \RuntimeException(
                'Invalid configuration provided for ElasticTool-Laravel!'
            );
        }

        $this->app->bind(
            'elastic.tool',
            function (Application $app) {
                return app(ElasticTool::class);
            }
        );

        $this->app->singleton(
            ElasticTool::class,
            function (Application $app, $arguments) use ($config) {
                $logger = $app->make(LoggerInterface::class);

                return new ElasticTool($config, $logger);
            }
        );
    }

    /**
     * @return void
     */
    protected function prepareCommands()
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands(
            [
                ListCommand::class,
                CreateIndexCommand::class,
                UpdateIndexCommand::class,
                DeleteIndexCommand::class,
                OpenIndexCommand::class,
                CloseIndexCommand::class,
                RefreshIndexCommand::class,
                StatsIndexCommand::class,
            ]
        );
    }

    /**
     * @return string
     */
    protected function getConfigFile()
    {
        return __DIR__ . '/config/elastic_tool.php';
    }

}
