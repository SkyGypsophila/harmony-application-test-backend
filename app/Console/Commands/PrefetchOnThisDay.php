<?php

namespace App\Console\Commands;

use App\Services\OnThisDayExecuter;
use Illuminate\Console\Command;
use Illuminate\Support\Benchmark;
use Illuminate\Validation\ValidationException;

class PrefetchOnThisDay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'onthisday:prefetch
        {--lang= : languages}
        {--type= : event types}';
        //{--variant : language variant}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prefetch onthisday snapshots into DB and cache.';

    /**
     * Execute the console command.
     * @throws ValidationException
     */
    public function handle(OnThisDayExecuter $executer): void
    {
        $langs = $this->option('lang')
            ? array_map('trim', explode(',', $this->option('lang')))
            : config('onthisday.langs');

        $types = $this->option('type')
            ? array_map('trim', explode(',', $this->option('type')))
            : config('onthisday.types');

        [$result, $ms] = Benchmark::value(function () use ($executer, $langs, $types) {
            $executer->validateOptions($langs, $types)
                ->fetch()
                ->trimPayload()
                ->synchronize();

            return true;
        });

        $this->info(sprintf('Prefetch done. Time: %.2fms', $ms));
    }
}
