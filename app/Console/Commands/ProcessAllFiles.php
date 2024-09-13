<?php

namespace App\Console\Commands;

use App\Jobs\ProcessFile;
use App\Models\File;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessAllFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kantine:process-all-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Launch process for all files';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (env('APP_ENV') !== 'local') {
            Log::info('We do not want to process all files in PROD environment');
            return 0;
        }
        Log::info('Launching process for all files');
        $files = File::all()->sortBy('datetime')->sortBy('name');
        foreach($files as $file) {
            Log::info('Processing file ' . $file->name . ' ' . $file->hash);
            $file->state = 'todo';
            $file->message = null;
            $file->save();
            ProcessFile::dispatch($file);
        }
        return 0;
    }
}
