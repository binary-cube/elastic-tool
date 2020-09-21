<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool\Support\Laravel\Console\Commands\Helpers;

use BinaryCube\ElasticTool\Index;

/**
 * Class ProxyIndexCommand
 */
class ProxyIndexCommand
{

    /**
     * @var Index
     */
    protected $index;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * Constructor.
     *
     * @param Index         $index
     * @param callable|null $callback
     */
    public function __construct(Index $index, callable $callback = null)
    {
        $this->index = $index;

        $this->callback = (
            \is_callable($callback)
                ? $callback
                : (function () {
                })
        );
    }

    /**
     * @return void
     */
    protected function callback()
    {
        \call_user_func($this->callback);
    }

    /**
     * @param array $log
     *
     * @return void
     */
    public function open(array &$log)
    {
        $log['summary']['open_index'] = '* Opening index';
        $this->callback();

        $this->index->open();

        $log['summary']['open_index'] = '* Index was opened';
        $this->callback();
    }

    /**
     * @param array $log
     *
     * @return void
     */
    public function close(array &$log)
    {
        $log['summary']['close_index'] = '* Closing index';
        $this->callback();

        $this->index->close();

        $log['summary']['close_index'] = '* Index was closed';
        $this->callback();
    }

    /**
     * @param array $log
     *
     * @return void
     */
    public function refresh(array &$log)
    {
        $log['summary']['refresh_index'] = '* Refreshing index';
        $this->callback();

        $this->index->refresh();

        $log['summary']['refresh_index'] = '* Index was refreshed';
        $this->callback();
    }

    /**
     * @param array $log
     *
     * @return void
     */
    public function create(array &$log)
    {
        $log['summary']['create_index'] = '* Creating index';
        $this->callback();

        $this->index->create();

        $log['summary']['create_index'] = '* Index was created';
        $this->callback();
    }

    /**
     * @param array $log
     *
     * @return void
     */
    public function update(array &$log)
    {
        $log['summary']['update_index'] = '* Updating index';
        $this->callback();

        $this->index->update();

        $log['summary']['update_index'] = '* Index was updated';
        $this->callback();
    }

    /**
     * @param array $log
     *
     * @return void
     */
    public function updateMapping(array &$log)
    {
        $log['summary']['apply_mapping'] = '* Applying index mapping';
        $this->callback();

        $this->index->updateMapping();

        $log['summary']['apply_mapping'] = '* Index mapping was applied';
        $this->callback();
    }

    /**
     * @param array $log
     *
     * @return void
     */
    public function delete(array &$log)
    {
        $log['summary']['delete_index'] = '* Deleting index';
        $this->callback();

        $this->index->delete();

        $log['summary']['delete_index'] = '* Index was deleted';
        $this->callback();
    }

}
