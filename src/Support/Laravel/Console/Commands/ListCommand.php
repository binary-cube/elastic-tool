<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool\Support\Laravel\Console\Commands;

/**
 * Class ListCommand
 */
class ListCommand extends BaseCommand
{

    const
        GROUP_ALL      = 'all',
        GROUP_MAPPINGS = 'mappings',
        GROUP_INDICES  = 'indices';

    /**
     * @var array
     */
    protected $allowedGroups = [
        self::GROUP_ALL      => true,
        self::GROUP_MAPPINGS => true,
        self::GROUP_INDICES  => true,
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
    protected $description = 'Listing registered mappings and indices';

    /**
     * Execute the console command.
     *
     * @return integer
     */
    public function handleInternal()
    {
        $group = $this->input->getArgument('group');
        $group = isset($this->allowedGroups[$group]) ? $group : self::GROUP_ALL;

        $rows = [];

        switch ($group) {
            case self::GROUP_ALL:
                $rows = $this->buildAllRow();
                break;

            case self::GROUP_MAPPINGS:
                $rows = $this->buildMappingRows();
                break;

            case self::GROUP_INDICES:
                $rows = $this->buildIndicesRows();
                break;
        }

        $this->table(['TYPE', 'GROUP', 'ID', 'NAME', 'USING MAPPING ID'], $rows, 'box');

        return 0;
    }

    /**
     * @return array
     */
    protected function buildAllRow()
    {
        return (
            \array_merge(
                $this->buildMappingRows(),
                $this->buildIndicesRows(),
            )
        );
    }

    /**
     * @return array
     */
    protected function buildMappingRows()
    {
        $rows = [];

        foreach ($this->esTool->container()->mappings()->all() as $mapping) {
            $rows[] = ['MAPPING', $mapping->id(), $mapping->name()];
        }

        return $rows;
    }

    /**
     * @return array
     */
    protected function buildIndicesRows()
    {
        $rows = [];

        foreach ($this->esTool->container()->indices()->all() as $index) {
            $rows[] = [
                'INDEX',
                ($index->group() ?? '-'),
                $index->id(),
                $index->name(),
                ($index->isHavingMappingSet() ? $index->mapping()->id() : '-'),
            ];
        }

        return $rows;
    }

}
