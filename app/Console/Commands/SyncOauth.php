<?php

namespace App\Console\Commands;

use App\Services\OauthServices;
use Illuminate\Console\Command;

class SyncOauth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:oauth';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Oauth';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(OauthServices $oauthServices)
    {
        parent::__construct();
        $this->oauthServices = $oauthServices;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->oauthServices->sync();
    }
}
