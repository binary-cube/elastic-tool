<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool\Support\Laravel\Console\Commands;

use BinaryCube\ElasticTool\Index;
use Symfony\Component\Console\Cursor;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class StatsIndexCommand
 */
class StatsIndexCommand extends BaseIndexCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '
                            elastic-tool:stats-index
                            {--i|interval= : Refresh stats using the provided interval (in seconds)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Stats Index';

    /**
     * Execute the console command.
     *
     * @return integer
     */
    public function handleInternal()
    {
        $indices  = $this->sanitize((string) $this->input->getArgument('index'));
        $group    = $this->input->getOption('group');
        $interval = $this->input->getOption('interval');

        $indices = $this->mapIndices($indices, $group);

        if (isset($interval)) {
            $interval = empty($interval = \abs($interval)) ? null : \max(1, $interval);
        }

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

        $output = new ConsoleOutput(
            $this->output->getVerbosity(),
            $this->output->isDecorated(),
            $this->output->getFormatter()
        );

        $rows    = [];
        $frame   = null;
        $cursor  = new Cursor($output);
        $section = $output->section();

        while (true) {
            foreach ($indices['ok'] as $index) {
                $id      = $index['element']->id();
                $element = $index['element'];

                if (empty($row = (isset($rows[$id]) ? $rows[$id] : []))) {
                    $row = [
                        'id'                   => $index['info']['id'],
                        'name'                 => $index['info']['name'],
                        'mapping'              => $index['info']['mapping'],
                        'status'               => $section->getFormatter()->format('<fg=black;bg=green>   OK   </>'),
                        'health'               => $section->getFormatter()->format('<fg=black;bg=green>   GREEN   </>'),
                        'docs.count'           => 0,
                        'docs.deleted'         => 0,
                        'segments'             => 0,
                        'store.size'           => 0,
                    ];
                }

                try {
                    $this->process($row, $element, []);
                } catch (\Exception $exception) {
                    $row['status'] = $section->getFormatter()->format('<fg=white;bg=red> NOT OK </>');
                    $row['health'] = $section->getFormatter()->format('<fg=white;bg=red> UNKNOWN </>');
                }

                $rows[$element->id()] = $row;
            }//end foreach

            $frame = $this->catchOutputToString(function () use ($rows) {
                $this->table(
                    [
                        'ID', 'NAME', 'MAPPING ID', 'STATUS', 'HEALTH',
                        'DOCS. COUNT', 'DOCS. DELETED',
                        'TOTAL SEGMENTS', 'TOTAL STORE SIZE',
                    ],
                    $rows,
                    'box'
                );
            });

            $section->overwrite($frame);

            if (! isset($interval)) {
                break;
            }

            \usleep((int) (1e6 * $interval));
        }//end while

        return 0;
    }

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
        $stats = $index->stats();

        if ($stats['summary']['health'] !== 'green') {
            $row['health'] = $this->output->getFormatter()->format('<fg=black;bg=yellow>   ' . $stats['summary']['health'] . '   </>');
        }

        $row['docs.count']   = $stats['summary']['docs.count'];
        $row['docs.deleted'] = $stats['summary']['docs.deleted'];
        $row['segments']     = $stats['detailed']['total']['segments']['count'];
        $row['store.size']   = $stats['summary']['store.size'];
    }

}
