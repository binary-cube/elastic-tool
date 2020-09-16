<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool\Support\Laravel\Console\Commands;

use Symfony\Component\Console\Helper\TableSeparator;

/**
 * Class ListCommand
 */
class ListCommand extends BaseCommand
{

    const
        GROUP_ALL     = 'all',
        GROUP_SCHEMAS = 'schemas',
        GROUP_INDICES = 'indices';

    /**
     * @var array
     */
    protected $allowedGroups = [
        self::GROUP_ALL,
        self::GROUP_SCHEMAS,
        self::GROUP_INDICES,
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '
                            elastic-tool:list
                            {group=all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listing container options like: schemas, indices. Default is `all`.';

    /**
     * Execute the console command.
     *
     * @return integer
     */
    public function handleInternal()
    {
        $group = $this->input->getArgument('group');
        $group = \in_array($group, $this->allowedGroups) ? $group : self::GROUP_ALL;

        $rows = [];

        switch ($group) {
            case self::GROUP_ALL:
                $rows = $this->buildAllRow();
                break;

            case self::GROUP_SCHEMAS:
                $rows = $this->buildSchemaRows();
                break;

            case self::GROUP_INDICES:
                $rows = $this->buildIndicesRows();
                break;
        }

        $this->table(['Group', 'Values'], $rows);

        return 0;
    }

    /**
     * @return array
     */
    protected function buildAllRow()
    {
        $separator = new TableSeparator();

        return (
            \array_merge(
                [],
                $this->buildSchemaRows(),
                [$separator],
                $this->buildIndicesRows(),
            )
        );
    }

    /**
     * @return array
     */
    protected function buildSchemaRows()
    {
        $result = [];
        $items  = [];

        foreach ($this->esTool->container()->schemas()->all() as $id => $schema) {
            $items[] = \vsprintf('%s (%s)', [$id, $schema->name()]);
        }

        $result[] = [
            'schemas',
            \implode(PHP_EOL, $items),
        ];

        return $result;
    }

    /**
     * @return array
     */
    protected function buildIndicesRows()
    {
        $result = [];
        $items  = [];

        foreach ($this->esTool->container()->indices()->all() as $id => $index) {
            $items[] = \vsprintf('%s (%s)', [$id, $index->name()]);
        }

        $result[] = [
            'indices',
            \implode(PHP_EOL, $items),
        ];

        return $result;
    }

}
