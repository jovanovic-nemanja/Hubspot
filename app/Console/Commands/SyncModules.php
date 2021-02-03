<?php

namespace App\Console\Commands;

use App\Services\SyncServices;
use Illuminate\Console\Command;

class SyncModules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:modules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Modules To Database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SyncServices $syncServices)
    {
        parent::__construct();
        $this->syncServices = $syncServices;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->syncServices->syncModules();
    }
}
