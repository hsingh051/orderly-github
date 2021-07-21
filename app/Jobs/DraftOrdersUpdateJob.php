<?php namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;


use App\Order;
use App\Customer;
use OhMyBrew\ShopifyApp\Facades\ShopifyApp;
use Log;

class DraftOrdersUpdateJob implements ShouldQueue
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

        Log::debug('draft orders update' . print_r($this->data, true));

        //Only update Draft Orders
        $order = Order::where('draft_id', $this->data->id)->where('shopify_status', 'draft')->first();

        if($order == null) {
            return response('Draft Order not Found', 200);
        }

        $order->draft_order = json_encode($this->data, true);
        $order->amount = $this->data->total_price;
        $order->email = $this->data->email;
        $order->draft_name = $this->data->name;
        if (isset($this->data->customer->id)) {
            $customer = Customer::where('shopify_customer_id', $this->data->customer->id)->orWhere('email', $this->data->email)->first();
            if (isset($customer)) {
                $order->customer_id = $customer->id;
            } else {
                $shop = \OhMyBrew\ShopifyApp\Models\Shop::where([
                    ['shopify_domain' , '=', $this->shopDomain]
                ])->first();
                
                $newCustomer = new Customer();
                $newCustomer->shop_id = $shop->id;
                $newCustomer->shopify_customer_id = $this->data->customer->id;
                if (isset($this->data->customer->first_name)) {
                    $newCustomer->first_name = $this->data->customer->first_name;
                }
                if (isset($this->data->customer->last_name)) {
                    $newCustomer->last_name = $this->data->customer->last_name;
                }
                $newCustomer->email = $this->data->email;
                $newCustomer->save();

                $savedCustomer = Customer::where('shopify_customer_id', $this->data->customer->id)->first();
                $order->customer_id = $savedCustomer->id;
            }
        }
        $order->save();

        return response('Draft Order Updated', 200);
    }
}
