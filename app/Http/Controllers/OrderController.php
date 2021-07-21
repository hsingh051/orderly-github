<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
use OhMyBrew\BasicShopifyAPI;
use OhMyBrew\ShopifyApp\Facades\ShopifyApp;
use App\Order;
use App\Setting;
use App\Customer;
use App\Jobs\CheckTransactionStatusJob;
use Carbon\Carbon;
use DB;
use Log;

// require 'vendor/autoload.php';

class OrderController extends Controller
{

    public function adminLink(Request $request)
    {
        $draft_id = $request->query('id');
        $shop = $request->query('shop');

        $order = Order::where('draft_id', $draft_id)->first();

        if ($order == null) {
            return 'no draft order saved';
        }
        
        // if (empty($order->order_id)) {
            //     \Session::flash('error', 'Please wait another minute for the order to sync with our database');
            //     return view('error-order');
            // }
            
        $store = ShopifyApp::shop();
            
        $setting = Setting::where('shop_id', $store->id)->first();

        $customer = $order->customer()->first();

        if (empty($setting)) {
            \Session::flash('error', 'Please setup your payment gateway before you can charge a card on file');
            return redirect('/settings');
        }
        
        $response = $this->getCustomerProfile($order, $setting, $customer->gateway_profile_id);


        if ($response['error'] == "profile") {
            $errorMessages = $response['response']['messages'];
            $errorCode = $response['response']['code'];
            $errorText = $response['response']['text'];

            $path = 'https://' . $store->shopify_domain . '/admin/draft_orders/' . $order->draft_id;
            $uri = '/draft_orders/' . $order->draft_id;

            return view('error-customer', compact('customer', 'errorMessages', 'errorCode', 'errorText', 'path', 'uri'));
            
        } elseif ( ($response['response'] != null) && ($response['response']->getMessages()->getResultCode() == "Ok") ) {
            $profile = $response['profile'];
            $cards = $response['cards'];
            $order = $response['order'];

            // dd($order, json_decode($order->draft_order), json_decode($order->draft_order)->line_items);

            $draftOrder = json_decode($order->draft_order);

            return view('chooseCreditCard', compact('customer', 'profile', 'cards', 'order', 'draftOrder'));
        
        } else {
            $errorMessages = $response['response']->getMessages()->getMessage();
            $errorCode = $errorMessages[0]->getCode();
            $errorText = $errorMessages[0]->getText();
            
            $path = 'https://' . $store->shopify_domain . '/admin/draft_orders/' . $order->draft_id;
            $uri = '/draft_orders/' . $order->draft_id;
            // \Session::flash('error', $errorText);
            return view('error', compact('path', 'errorMessages', 'errorCode', 'errorText', 'uri'));

        }

        
    }

    public function postCustomerMetafield($order, $namespace, $value)
    {
        $store = ShopifyApp::shop();
        $data = array(
            'metafield' => array( 
                'namespace' => $namespace,
                'key' => 'orderly',
                'value'=> $value,
                'value_type'=> 'string'
            )
        );

        $customer = $order->customer()->first();

        $metafields = $store->api()->rest('POST', '/admin/customers/' . $customer->shopify_customer_id . '/metafields.json', $data);

        return $metafields;
    }

    public function getCustomerProfileIds() 
    {

        $store = ShopifyApp::shop();
        
        $setting = Setting::where('shop_id', $store->id)->first();
        
        // Common setup for API credentials
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName($setting->authorize_payment_api_login_id);
        $merchantAuthentication->setTransactionKey($setting->authorize_payment_transaction_key);

        $refId = 'ref'.time();

        // Get all existing customer profile ID's
        $request = new AnetAPI\GetCustomerProfileIdsRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $controller = new AnetController\GetCustomerProfileIdsController($request);
        if (env('AUTHORIZE_ENV', '') == 'SANDBOX') {
            $response = $controller->executeWithApiResponse( \net\authorize\api\constants\ANetEnvironment::SANDBOX);
        } else {
            $response = $controller->executeWithApiResponse( \net\authorize\api\constants\ANetEnvironment::PRODUCTION);
        }
        
        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok") ) {
            echo "GetCustomerProfileId's SUCCESS: " . "\n";
            $profileIds[] = $response->getIds();
            echo "There are " . count($profileIds[0]) . " Customer Profile ID's for this Merchant Name and Transaction Key" . "\n";
        } else {
            echo "GetCustomerProfileId's ERROR :  Invalid response\n";
            $errorMessages = $response->getMessages()->getMessage();
            echo "Response : " . $errorMessages[0]->getCode() . "  " .$errorMessages[0]->getText() . "\n";
        }
        return $response;
    }

    public function getCustomerProfile($order, $setting, $customerId = null) 
    {
        // Log::debug('get customer profile email ' . $order->email);

        $store = ShopifyApp::shop();
        
        $setting = Setting::where('shop_id', $store->id)->first();

        // Common setup for API credentials
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName($setting->authorize_payment_api_login_id);
        $merchantAuthentication->setTransactionKey($setting->authorize_payment_transaction_key);
        
        $refId = 'ref'.time();

        $customerprofile = new AnetAPI\CustomerProfileType();
        $customerprofile->setEmail($order->email);

      
        // $request = new AnetAPI\CreateCustomerProfileRequest();
        // $request->setMerchantAuthentication($merchantAuthentication);
        // $request->setRefId($refId);
        // $request->setProfile($customerprofile);
        
        $request = new AnetAPI\GetCustomerProfileRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setEmail($order->email);
        $request->setUnmaskExpirationDate(true);

        if ($customerId) {
            $request->setCustomerProfileId($customerId);
        }


        $controller = new AnetController\GetCustomerProfileController($request);
        if (env('AUTHORIZE_ENV', '') == 'SANDBOX') {
            $response = $controller->executeWithApiResponse( \net\authorize\api\constants\ANetEnvironment::SANDBOX);
        } else {
            $response = $controller->executeWithApiResponse( \net\authorize\api\constants\ANetEnvironment::PRODUCTION);
        }

        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok") ) {
            // echo "GetCustomerProfile SUCCESS : " .  "\n";
            $profileSelected = $response->getProfile();
            $paymentProfilesSelected = $profileSelected->getPaymentProfiles();

            $profile = array();
            $profileSelected = $response->getProfile();
            $profile['cust_id'] = $profileSelected->getCustomerProfileId();
            $profile['email_address'] = $profileSelected->getEmail();
            $profile['description'] = $profileSelected->getDescription();
            $profile['payment_profiles'] = $profileSelected->getPaymentProfiles();
            $profile['profile_type'] = $profileSelected->getProfileType();
            $proflie['payment_profiles_preferred'] = array();

            // dd($profile['payment_profiles']);

            // $paymentProfiles = array();
            $cards = array();
            $count = 0;
            foreach ($profile['payment_profiles'] as $key => $value) {
                $paymentProfile = $profile['payment_profiles'][$count];
                $cardFirst = $paymentProfile->getBillTo()->getFirstName();
                $cardLast = $paymentProfile->getBillTo()->getLastName();
                $cardCompany = $paymentProfile->getBillTo()->getCompany();
                $cardAddress = $paymentProfile->getBillTo()->getAddress();
                $cardCity = $paymentProfile->getBillTo()->getCity();
                $cardState = $paymentProfile->getBillTo()->getState();
                $cardZip = $paymentProfile->getBillTo()->getZip();
                $paymentProfileId = $paymentProfile->getCustomerPaymentProfileId();
                $creditCard = $paymentProfile->getPayment()->getCreditCard();
                $cardNumber = $creditCard->getCardNumber();
                $cardExp = $creditCard->getExpirationDate();
                $cardType = $creditCard->getCardType();

                $card = new \stdClass();
                $card->paymentProfileId = $paymentProfileId;
                $card->cardNumber = $cardNumber;
                $card->cardExp = $cardExp;
                $card->cardType = $cardType;
                $card->cardFirst = $cardFirst;
                $card->cardLast = $cardLast;
                $card->cardCompany = $cardCompany;
                $card->cardAddress = $cardAddress;
                $card->cardCity = $cardCity;
                $card->cardState = $cardState;
                $card->cardZip = $cardZip;
                array_push($cards, $card);

                $count++;
            }
            
            // echo "Profile Has " . count($paymentProfilesSelected). " Payment Profiles" . "\n";
            
            $responseArray = array(
                'profile' => $profile, 
                'cards' => $cards, 
                'order' => $order,
                'error' => '', 
                'response' => $response
            );

            return $responseArray;

        } else {
            // echo "ERROR :  GetCustomerProfile: Invalid response\n";
            $errorMessages = $response->getMessages()->getMessage();
            $errorCode = $errorMessages[0]->getCode();
            $errorText = $errorMessages[0]->getText();
            // echo "Response : " . $errorMessages[0]->getCode() . "  " .$errorMessages[0]->getText() . "\n";
            
            $response = array( 
                'error' => 'profile',
                'response' =>
                array(
                    'messages' => $errorMessages,
                    'code' => $errorCode,
                    'text' => $errorText
                )
            );
        }
        // Log::debug('Get Customer Profile for this order -> ' . $order . 'This customer returned -> ' . $response );
        
        return $response;
    }

    public function chargeCustomerPaymentProfile(Request $request)
    {
        // dd($request->order);
        $profileid = $request->customer_id;
        $amount = $request->amount;
        $paymentProfileId = $request->card;
        $order = Order::where('id', $request->order)->first();

        $store = ShopifyApp::shop();
            
        $setting = Setting::where('shop_id', $store->id)->first();

        if (empty($setting)) {
            \Session::flash('error', 'Please setup your payment gateway');
            return redirect('/settings');
        }

        // Common setup for API credentials
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName($setting->authorize_payment_api_login_id);
        $merchantAuthentication->setTransactionKey($setting->authorize_payment_transaction_key);
        
        // Set the transaction's refId
        $refId = 'ref' . time();
        $profileToCharge = new AnetAPI\CustomerProfilePaymentType();
        $profileToCharge->setCustomerProfileId($profileid);
        $paymentProfile = new AnetAPI\PaymentProfileType();
        $paymentProfile->setPaymentProfileId($paymentProfileId);
        $profileToCharge->setPaymentProfile($paymentProfile);
        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType( "authCaptureTransaction"); 
        $transactionRequestType->setAmount($amount);
        $transactionRequestType->setProfile($profileToCharge);
        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setRefId( $refId);
        $request->setTransactionRequest( $transactionRequestType);
        $controller = new AnetController\CreateTransactionController($request);
        if (env('AUTHORIZE_ENV', '') == 'SANDBOX') {
            $response = $controller->executeWithApiResponse( \net\authorize\api\constants\ANetEnvironment::SANDBOX);
        } else {
            $response = $controller->executeWithApiResponse( \net\authorize\api\constants\ANetEnvironment::PRODUCTION);
        }

        $tresponseErrors = $response->getTransactionResponse()->getErrors();

        if ( is_array($tresponseErrors) && !empty($tresponseErrors)) {
            if ($tresponseErrors[0]->getErrorCode()) {

                $errorCode = $tresponseErrors[0]->getErrorCode();
                $errorText = $tresponseErrors[0]->getErrorText();

                session()->flash('error', $errorText);

                return back();
            }
        }

        if ($response != null) {
            if ($response->getMessages()->getResultCode() == "Ok") {
                $tresponse = $response->getTransactionResponse();
                
                if ($tresponse != null && $tresponse->getMessages() != null) {
                    

                    // echo " Transaction Response code : " . $tresponse->getResponseCode() . "\n";
                    // echo  "Charge Customer Profile APPROVED  :" . "\n";
                    // echo " Charge Customer Profile AUTH CODE : " . $tresponse->getAuthCode() . "\n";
                    // echo " Charge Customer Profile TRANS ID  : " . $tresponse->getTransId() . "\n";
                    // echo " Code : " . $tresponse->getMessages()[0]->getCode() . "\n"; 
                    // echo " Description : " . $tresponse->getMessages()[0]->getDescription() . "\n";
                    
                    $order->gateway_transaction_id = $tresponse->getTransId();
                    
                    if ($tresponse->getResponseCode() == '1') {
                        $order->gateway_status = 'capturedPendingSettlement';
                        $this->updateShopifyDraft($order, 'pending');
                        $order->save();
                        return $this->dashboard($order, 'success', 'Congrats! The transaction has been Approved. The card was charged - $' . $order->amount);
                    } elseif ($tresponse->getResponseCode() == '2') {
                        $order->gateway_status = 'Declined: Response Code - ' . $tresponse->getResponseCode();
                        $order->save();
                        $this->updateShopifyDraft($order, 'pending');
                        return $this->dashboard('error', 'Card Declined');
                    } elseif ($tresponse->getResponseCode() == '3') {
                        $order->gateway_status = 'Declined: Response Code - ' . $tresponse->getResponseCode();
                        $order->save();
                        $this->updateShopifyDraft($order, 'pending');
                    } elseif ($tresponse->getResponseCode() == '4') {
                        $order->gateway_status = 'Declined - Fraud Detected: Response Code - ' . $tresponse->getResponseCode();
                        $order->save();
                        $this->updateShopifyDraft($order, 'pending');
                    } else {
                        $order->gateway_status = 'Error: Response Code - ' . $tresponse->getResponseCode();
                        $order->save();
                        $this->updateShopifyDraft($order, 'pending');
                    }

                    

                } else {
                    $order->gateway_status = 'Error: Response Code - ' . $tresponse->getResponseCode();
                    $order->save();
                    $this->updateShopifyDraft($order, 'pending');
                }
            } else {
                
                $order->gateway_status = 'Error: Response Code - ' . $response->getResponseCode();
                $order->save();
                $this->updateShopifyDraft($order, 'pending');
            }
        } else {
            echo  "No response returned \n";
        }

        return $response;
    }

    public function updateShopifyDraft($order, $status)
    { 
        $shop = ShopifyApp::shop();

        //CUSTOMER METAFIELD
        $metafields = $this->postCustomerMetafield($order, 'draft_order_id', $order->draft_id);
        // Log::debug('updateShopifyDraft postCustomerMetafield ' . print_r($metafields, true));

        if ($status == 'pending') {
            $request = $shop->api()->rest('PUT', '/admin/api/2019-07/draft_orders/' . $order->draft_id . '/complete.json', ['payment_pending' => 'true']);
            $order->shopify_status = 'pending';
            $order->save();
        } else if ($status == 'complete') {
            $request = $shop->api()->rest('PUT', '/admin/api/2019-07/draft_orders/' . $order->draft_id . '/complete.json');
        }
        
        return $request;
    }

    public function dashboard($order = '', $flashType = '', $flashMessage = '') 
    {
        // LANDING PAGE
        $shop = ShopifyApp::shop();
        $setting = Setting::where('shop_id', $shop->id)->first();
        $orders = $shop->orders()->where('order_id', '!=', 'null')->orderBy('created_at', 'desc')->paginate(25);

        \Session::flash($flashType, $flashMessage);


        // Get today's activity 
        $ordersToday = $shop->orders()->where('order_id', '!=', 'null')->where('created_at', '>=', Carbon::today())->get();
        $ordersCount = $ordersToday->count();

        $salesToday = $ordersToday->sum('amount');
        
        $pendingToday =     $shop->orders()->where('order_id', '!=', 'null')->where('gateway_status', 'capturedPendingSettlement')->sum('amount');
        $ordersPending =    $shop->orders()->where('order_id', '!=', 'null')->where('gateway_status', 'capturedPendingSettlement')->get();
        $ordersFraud =      $shop->orders()->where('order_id', '!=', 'null')->where('gateway_status', 'FDSPendingReview')->get();

        return view('order', compact('orders', 'shop', 'order', 'setting', 'ordersToday', 'ordersCount', 'salesToday', 'pendingToday', 'ordersPending', 'ordersFraud'));
    }

    public function checkTransactionSatusJob()
    {
        dispatch(new CheckTransactionStatusJob());
    }

    public function checkTransactionStatus(Request $request)
    {
        $orderId = $request->order_id;

        $order = Order::where('id', $orderId)->first();
        
        if ($order->gateway_status == 'prepare') {
            return redirect('order');
        }

        $response = $this->requestTransactionDetails($order->gateway_transaction_id, $order);

        $transactionStatus = $response->getTransaction()->getTransactionStatus();

        $transactionId = $response->getTransaction()->getTransId();

        $order->gateway_status = $transactionStatus;
        $order->save();

        // if ($order->gateway_status == 'settledSuccessfully') {
        //     $this->postOrderTransactionSuccess($order);   ?
        // }

        return redirect('order');
    }

    public function postOrderTransactionSuccess($order)
    {
        $shop = ShopifyApp::shop();

        $data = array(
            'transaction' => array( 
                'kind' => 'capture',
                'status' => 'success',
                'amount' => $order->amount,
                'order_id' => $order->order_id,
            )
        );

        $putRequest = $shop->api()->rest('POST', '/admin/api/2019-07/orders/' . $order->order_id . '/transactions.json', $data);
        
        // dd($putRequest);
        
        // $order->shopify_status = 'paid';
        // $order->save();


        // if (isset($requestOrder)) {
            // $order->shopify_status = $requestOrder->body->order->financial_status;
        // }
    }

    public function getRequestOrder($order)
    {
        $shop = ShopifyApp::shop();

        $data = array(
            'transaction' => array( 
                'kind' => 'capture',
                'status' => 'success',
                'amount' => $order->amount,
                'order_id' => $order->order_id,
            )
        );

        $getRequest = $shop->api()->rest('GET', '/admin/api/2019-07/orders/' . $order->order_id . '.json', $data);

        return $getRequest;
    }

    public function getTransactionDetails() 
    {
        
        //Get All orders that are approved

        $approvedOrders = Order::where('gateway_status', 'capturedPendingSettlement')->get();
        
        foreach ($approvedOrders as $order) {
            $transactionId = $order->gateway_transaction_id;
            $response = $this->requestTransactionDetails($transactionId, $order);
            
            $transactionStatus = $response->getTransaction()->getTransactionStatus();
            $order->gateway_status = $transactionStatus;
            $order->save();
        }

        
    }

    public function requestTransactionDetails($transactionId, $order)
    {
        $store = ShopifyApp::shop();
            
        $setting = Setting::where('shop_id', $store->id)->first();

        if (empty($setting)) {
            \Session::flash('error', 'Please setup your payment gateway before you can charge a card on file');
            return redirect('/settings');
        }

        // Common setup for API credentials
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName($setting->authorize_payment_api_login_id);
        $merchantAuthentication->setTransactionKey($setting->authorize_payment_transaction_key);
        
        // Set the transaction's refId
        $refId = 'ref' . time();


        $request = new AnetAPI\GetTransactionDetailsRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setTransId($transactionId);

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
            
            if($transactionStatus == 'settledSuccessfully') {
                $order->gateway_status = $transactionStatus;
                $order->save();

                // $data = array(
                //     'order' => array( 
                //         'financial_status' => 'paid'
                //     )
                // );
                // $shop = ShopifyApp::shop();

                // $request = $shop->api()->rest('PUT', '/admin/api/2019-07/orders/' . $order->order_id . '.json', $data);
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

    public function log(Request $request)
    {
        Log::debug('ANET Log entry ' . print_r($request, true));
    }

}