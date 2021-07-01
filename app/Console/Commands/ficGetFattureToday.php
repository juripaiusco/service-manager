<?php

namespace App\Console\Commands;

use App\Http\Controllers\FattureInCloudAPI;
use Illuminate\Console\Command;

class ficGetFattureToday extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fic:getFattureToday';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importazione fatture da FattureInCloud al DB MySQL nel giorno corrente';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $fic = new FattureInCloudAPI();
        $fic->getFattureToday();

        return 0;
    }
}
