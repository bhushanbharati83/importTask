<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Illuminate\Foundation\Exceptions\Renderer\Renderer;
use League\Csv\Reader;

class ProcessCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $csv = Render::createFromPath(storage_path("app/" . $this->path), 'r');
        $csv->setHeaderOffset(0);

        $records = iterator_to_array($csv->getRecords());

        // chunk into 500 rows per batch
        foreach (array_chunk($records, 500) as $chunk) {
            ProcessCsvChunk::dispatch($chunk);
        }
    }
}
