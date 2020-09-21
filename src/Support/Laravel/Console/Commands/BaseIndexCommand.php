<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool\Support\Laravel\Console\Commands;

use BinaryCube\ElasticTool\ElasticTool;
use BinaryCube\ElasticTool\Index;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Cursor;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use BinaryCube\ElasticTool\Support\Laravel\Console\Commands\Helpers\HasIndexMapper;
use BinaryCube\ElasticTool\Support\Laravel\Console\Commands\Helpers\HasIndexListing;

/**
 * Class BaseIndexCommand
 */
abstract class BaseIndexCommand extends BaseCommand
{
    use HasIndexMapper;
    use HasIndexListing;

    const INCLUDE_INDEX   = 'index';
    const INCLUDE_MAPPING = 'mapping';
    const INCLUDE_ALIASES = 'aliases';

    const STATUS_PENDING = ' PROCESSING ';
    const STATUS_OK      = '     OK     ';
    const STATUS_NOT_OK  = '   NOT OK   ';

    /**
     * @var array
     */
    protected $allowedIncludeOptions = [
        self::INCLUDE_INDEX   => true,
        self::INCLUDE_MAPPING => true,
    ];

    /**
     * Constructor.
     *
     * @param ElasticTool $esTool
     */
    public function __construct(ElasticTool $esTool)
    {
        parent::__construct($esTool);

        $this
            ->addArgument('index', InputArgument::REQUIRED, 'Index ID separated by comma or `all`')
            ->addOption('group', 'g', InputArgument::OPTIONAL, 'Indices that belong to the provided group');
    }

    /**
     * Execute the console command.
     *
     * @return integer
     */
    public function handleInternal()
    {
        $indices = $this->sanitize((string) $this->input->getArgument('index'));
        $group   = $this->input->getOption('group');
        $include = [];

        if ($this->input->hasOption('include')) {
            $include = $this->sanitize(\strtolower((string) $this->input->getOption('include')));
        }

        $indices = $this->mapIndices($indices, $group);

        if (empty($indices['ok']) && empty($indices['not-found'])) {
            $this->warn('No index found.');
            return 0;
        }

        if (! empty($indices['not-found'])) {
            foreach ($indices['not-found'] as $index) {
                $this->error($index);
            }

            return 0;
        }

        if (
            ! empty($include)
            && ! empty(\array_diff($include, \array_keys($this->allowedIncludeOptions)))
        ) {
            $this->error(\vsprintf(
                'Unknown `include` argument `%s`! Allowed values are: %s',
                [
                    \implode(',', $include),
                    \implode(',', \array_keys($this->allowedIncludeOptions)),
                ]
            ));

            return 0;
        }

        // Reindex.
        $indices['ok'] = \array_values($indices['ok']);

        $this->listIndices($indices['ok']);

        if (! $this->confirm('The listed indices will be affected. Do you wish to continue?')) {
            return 0;
        }

        $output = new ConsoleOutput(
            $this->output->getVerbosity(),
            $this->output->isDecorated(),
            $this->output->getFormatter()
        );

        $cursor  = new Cursor($output);
        $section = $output->section();
        $errors  = [];

        $rows = $this->prepareRows($indices['ok']);

        $this->render($rows, $section, $cursor);

        foreach ($indices['ok'] as $index) {
            $id      = $index['info']['id'];
            $element = $index['element'];

            try {
                $rows[$id]['summary'][] = (
                    '* Index exists: '
                    . (
                        $element->exists()
                            ? '<fg=black;bg=green> YES </>'
                            : '<fg=black;bg=green> NO </>'
                    )
                );

                $this->process($rows[$id], $element, $include, function () use ($rows, $section, $cursor) {
                    $this->render($rows, $section, $cursor);
                });

                $rows[$id]['status'] = $this->output->getFormatter()->format('<fg=black;bg=green>' . self::STATUS_OK . '</>');
            } catch (\Throwable $exception) {
                $errors[] = $exception->getMessage();

                $rows[$id]['status'] = $this->output->getFormatter()->format('<fg=white;bg=red>' . self::STATUS_NOT_OK . '</>');

                Log::error((string) $exception);
            }//end try

            $this->render($rows, $section, $cursor);
        }//end foreach

        if (! empty($errors)) {
            $this->error(\implode(PHP_EOL, $errors));

            $this->output->writeln('');
            $this->output->writeln('');

            $this->error('Something went wrong! Check the log for more information.');
        }

        return 0;
    }

    /**
     * @param array                $rows
     * @param ConsoleSectionOutput $section
     * @param Cursor               $cursor
     *
     * @return void
     */
    protected function render(array $rows, ConsoleSectionOutput $section, Cursor $cursor)
    {
        $section->overwrite($this->catchOutputToString(function ($input, $output) use ($rows) {
            $rows = \unserialize(\serialize($rows));

            foreach ($rows as $key => $row) {
                if ($row instanceof TableSeparator) {
                    continue;
                }

                $rows[$key]['summary'] = \implode(PHP_EOL, $row['summary']);
            }

            $table = new Table($output);

            $table
                ->setHeaders(['GROUP', 'ID', 'NAME', 'MAPPING ID', 'SUMMARY', 'STATUS'])
                ->addRows($rows)
                ->setStyle('box');

            $table
                ->setColumnWidth(4, 30)
                ->setColumnWidth(5, 12);

            $table->render();
        }));
    }

    /**
     * @param Index[] $indices
     *
     * @return array
     */
    protected function prepareRows(array $indices): array
    {
        $rows  = [];
        $count = \count($indices);

        for ($i = 0; $i < $count; $i++) {
            $index = $indices[$i];
            $id    = $index['info']['id'];

            $rows[$id] = [
                'group'   => $index['info']['group'],
                'id'      => $index['info']['id'],
                'name'    => $index['info']['name'],
                'mapping' => $index['info']['mapping'],
                'summary' => [],
                'status'  => $this->output->getFormatter()->format('<fg=black;bg=yellow>' . self::STATUS_PENDING . '</>'),
            ];

            if ($i < ($count - 1)) {
                $rows[] = new TableSeparator();
            }
        }

        return $rows;
    }

    /**
     * @param array         $row
     * @param Index         $index
     * @param array         $include
     * @param callable|null $callback
     *
     * @return void
     */
    abstract protected function process(array &$row, Index $index, array $include = [], callable $callback = null): void;

}
