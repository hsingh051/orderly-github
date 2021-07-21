<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Order;
use App\Setting;
use Carbon\Carbon;
use Mail;
use App\Mail\NewFraudDetection;
use OhMyBrew\ShopifyApp\Facades\ShopifyApp;
use OhMyBrew\ShopifyApp\Models\Shop;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

class CheckTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check status of all transactions';

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
        $orders = Order::where('gateway_status', '!=', 'settledSuccessfully')->where('order_id', '!=', null)->get();
        foreach ($orders as $order) {
            $transactionStatus = $this->checkTransactionStatus($order);
        }
    }

    public function checkTransactionStatus($order)
    {
        $shop = Shop::find($order->shop_id);
        $setting = Setting::where('shop_id', $order->shop_id)->first();

        if (empty($setting)) {
            return 'need settings filled out for customer';
        } else if (empty($order->order_id)) {
            return 'need order id';
        }

        // Common setup for API credentials
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName($setting->authorize_payment_api_login_id);
        $merchantAuthentication->setTransactionKey($setting->authorize_payment_transaction_key);
        
        // Set the transaction's refId
        $refId = 'ref' . time();

        $request = new AnetAPI\GetTransactionDetailsRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setTransId($order->gateway_transaction_id);

        $controller = new AnetController\GetTransactionDetailsController($request);

        if (env('AUTHORIZE_ENV', '') == 'SANDBOX') {
            $response = $controller->executeWithApiResponse( \net\authorize\api\constants\ANetEnvironment::SANDBOX);
        } else {
            $response = $controller->executeWithApiResponse( \net\authorize\api\constants\ANetEnvironment::PRODUCTION);
        }

        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok"))
        {
            echo "SUCCESS: Transaction Status:" . $response->getTransaction()->getTransactionStatus() . "\n";
            echo "                Auth Amount:" . $response->getTransaction()->getAuthAmount() . "\n";
            echo "                   Trans ID:" . $response->getTransaction()->getTransId() . "\n";

            $transactionStatus = $response->getTransaction()->getTransactionStatus();
            
            if ($transactionStatus == 'settledSuccessfully') {
                $order->gateway_status = $transactionStatus;

                $data = array(
                    'transaction' => array( 
                        'kind' => 'capture',
                        'status' => 'success',
                        'amount' => $order->amount,
                        'order_id' => $order->order_id,
                    )
                );

                $putRequest = $shop->api()->rest('POST', '/admin/api/2019-07/orders/' . $order->order_id . '/transactions.json', $data);
                
                // $getRequest = $shop->api()->rest('GET', '/admin/api/2019-07/orders/' . $order->order_id . '.json', $data);
                
                $order->shopify_status = 'paid';

                $order->save();
            }

            // CHECK FRAUD
            if ($response->getTransaction()->getFDSFilters() != null) {
                $filters = [];
                foreach($response->getTransaction()->getFDSFilters() as $item) {
                    $filter = new \stdClass();
                    $filter->name = $item->getName();
                    $filter->action = $item->getAction();
                    $filters[] = $filter;
                }
                $order->gateway_status = $transactionStatus;
                $order->fraud = json_encode($filters, true);
                
                $fraudFirstTime = false;
                if (!$order->fraud_status) {
                    $fraudFirstTime = true;
                }
                
                $order->fraud_status = 'Fraud Filter';
                $order->save();
                
                // Send Email
                // accounting@cellese.com

                if ( $fraudFirstTime ) {
                    Mail::to('accounting@cellese.com')->send(new NewFraudDetection($order));
                }
                
            }
        }
        else
        {
            echo "ERROR :  Invalid response\n";
            $errorMessages = $response->getMessages()->getMessage();
            echo "Response : " . $errorMessages[0]->getCode() . "  " .$errorMessages[0]->getText() . "\n";
        }

        return $response;
    }
}
