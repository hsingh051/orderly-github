<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OhMyBrew\ShopifyApp\Facades\ShopifyApp;
use OhMyBrew\ShopifyApp\Models\Shop;

class ShopifyWebhooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopify:webhooks {domain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get webhooks for shopify store';

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
        $shop = Shop::where('shopify_domain', $this->argument('domain'))->first();

        $getRequest = $shop->api()->rest('GET', '/admin/webhooks.json');

        dd($getRequest);
    }
}
