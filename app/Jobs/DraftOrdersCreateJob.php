<?php namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Log;

use App\Order;
use App\Customer;

use OhMyBrew\ShopifyApp\Facades\ShopifyApp;

class DraftOrdersCreateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Shop's myshopify domain
     *
     * @var string
     */
    public $shopDomain;

    /**
     * The webhook data
     *
     * @var object
     */
    public $data;

    /**
     * Create a new job instance.
     *
     * @param string $shopDomain The shop's myshopify domain
     * @param object $data    The webhook data (JSON decoded)
     *
     * @return void
     */
    public function __construct($shopDomain, $data)
    {
        $this->shopDomain = $shopDomain;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        
        Log::debug(print_r($this->data, true));
        
        //SHOP
        $shop = \OhMyBrew\ShopifyApp\Models\Shop::where([
            ['shopify_domain' , '=', $this->shopDomain]
        ])->first();

        //SETUP ORDER
        $order = new Order();
        
        //CUSTOMER
        if (isset($this->data->customer->id)) {
            $customerLookup = Customer::where('shopify_customer_id', $this->data->customer->id)->orWhere('email', $this->data->email)->first();
            if (empty($customerLookup)) {
                Log::debug('customer not found, making new Customer entry ' . $this->data->customer->id);
                
                $customer = new Customer();
    
                $customer->shop_id = $shop->id;
                $customer->shopify_customer_id = $this->data->customer->id;
                $customer->email = $this->data->email;
                if (isset($this->data->customer->first_name)) {
                    $customer->first_name = $this->data->customer->first_name;
                }
                if (isset($this->data->customer->last_name)) {
                    $customer->last_name = $this->data->customer->last_name;
                }
                $customer->save();
    
                $customerFind = Customer::where('shopify_customer_id', $this->data->customer->id)->first();
                $order->customer_id = $customerFind->id;
            } else {
                $order->customer_id = $customerLookup->id;
            }
        }


        //ORDER PROPERTIES
        $order->draft_order = json_encode($this->data, true);
        $order->shop_id = $shop->id;
        $order->draft_id = $this->data->id;
        $order->amount = $this->data->total_price;
        $order->shopify_status = 'draft';
        $order->gateway_status = 'prepare';
        $order->email = $this->data->email;
        $order->draft_name = $this->data->name;
        $order->gateway_transaction_id = null;

        Log::debug(print_r($order, true));
        $saved = $order->save();

        Log::debug(print_r($saved, true));
        return response('Draft Order Created', 200);
    }
}
