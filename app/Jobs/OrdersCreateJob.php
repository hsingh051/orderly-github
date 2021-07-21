<?php namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use OhMyBrew\ShopifyApp\Facades\ShopifyApp;
use App\Order;
use App\Shop;
use Log;

class OrdersCreateJob implements ShouldQueue
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
        $customerId = $this->data->customer->id;

        $lastOrderId = $this->data->customer->last_order_id;

        $shop = \OhMyBrew\ShopifyApp\Models\Shop::where([
            ['shopify_domain' , '=', $this->shopDomain]
        ])->first();
        

        // $customer = $store->api()->rest('GET', '/admin/api/2019-07/customers/' . $customerId . '.json');

        $customerMetafields = $shop->api()->rest('GET', '/admin/customers/' . $customerId . '/metafields.json');
        
        // $lastOrder = $shop->api()->rest('GET', '/admin/api/2019-07/orders/' . $lastOrderId . '.json');

        // Log::debug('shop -> ' . print_r($shop, true) . 'customer Metafields -> ' . print_r($customerMetafields, true) . ' last order -> ' . print_r($lastOrder, true));
        
        $allMetafields  = $customerMetafields->body->metafields;
        $draftOrderId = '';
        foreach ($allMetafields as $metafield) {
            if ($metafield->namespace == 'draft_order_id') {
                $draftOrderId = $metafield->value;
            }
        }

        if (empty($draftOrderId)) {
            return response('Order did not have a metafield for draft_order_id', 422);
        }

        // $draftOrderId = $customerMetafields->body->metafields[0]->value;

        // Log::debug('Customer Metafields -> ' . print_r($draftOrderId, true));
        
        $order = Order::where('draft_id', $draftOrderId)->first();

        // Log::debug('order find where draft_id = ' . $draftOrderId . ' eloquent response -> ' . print_r($order, true));

        $order->order_id = $lastOrderId;
        $order->order = json_encode($this->data);
        $order->order_name = $this->data->name;

        $data = array(
            'transaction' => array( 
                'kind' => 'capture',
                'status' => 'success',
                'amount' => $order->amount,
                'order_id' => $order->order_id,
            )
        );

        $putRequest = $shop->api()->rest('POST', '/admin/api/2019-07/orders/' . $order->order_id . '/transactions.json', $data);
        
        $getRequest = $shop->api()->rest('GET', '/admin/api/2019-07/orders/' . $order->order_id . '.json', $data);
        
        // $order->shopify_status = 'paid';
        $order->shopify_status = $getRequest->body->order->financial_status;

        // Log::debug($order->order_id);
        $order->save();

        return response('Order Created', 200);
    }
}
