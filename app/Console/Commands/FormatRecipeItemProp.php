<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\FormatRecipeItemPropService;

class FormatRecipeItemProp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'format:itemprop {path} {--filename=} {--title=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Format recipe itemprop';

    protected $formatItemPropService;
    /**
     * Create a new command instance.
     *
     * @return void
     */
        
    public function __construct(FormatRecipeItemPropService $formatItemPropService)
    {
        parent::__construct();
        $this->formatItemPropService = $formatItemPropService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $folderPath = $this->argument('path');
        $fileName = $this->option('filename');
        $title = $this->option('title');
        $this->formatItemPropService->formatAndWriteInExcel($folderPath, $fileName, $title);
    }
}
