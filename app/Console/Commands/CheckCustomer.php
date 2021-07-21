<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Order;
use App\Setting;
use App\Customer;
use Carbon\Carbon;
use OhMyBrew\ShopifyApp\Facades\ShopifyApp;
use OhMyBrew\ShopifyApp\Models\Shop;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

class CheckCustomer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks Customer Cards and updates tags on shopify';

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
     * @return mixed
     */
    public function handle()
    {
        //
    }
}
