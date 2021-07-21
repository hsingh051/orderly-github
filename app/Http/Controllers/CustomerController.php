<?php

namespace App\Http\Controllers;

use App\Customer;
use App\Setting;
use App\Card;
use App\Http\Controllers\OrderController;
use Illuminate\Http\Request;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
use OhMyBrew\ShopifyApp\Facades\ShopifyApp;
use Log;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $shop = ShopifyApp::shop();


        $customers = Customer::where('shop_id', $shop->id)->where('gateway_profile_id', '!=', null)->orderBy('created_at', 'desc')->paginate(15);

        return view('customer', compact('customers', 'shop'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $shop = ShopifyApp::shop();

        return view('customer-create');
    }

    public function createPrefilled($id)
    {
        $shop = ShopifyApp::shop();

        $customer = Customer::findOrFail($id);

        return view('customer-create-prefilled', compact('customer'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $store = ShopifyApp::shop();
        
        $setting = Setting::where('shop_id', $store->id)->first();

        $validatedData = $request->validate([
            'email' => 'required|email|max:255',
            'description' => 'max:255|nullable',
            'employee_name' => '',
            'employee_email' => '',
            'customer_type' => 'required|sometimes|nullable',
            'card_number' => 'min:13|max:16|nullable|sometimes|required_with:card_exp_month,card_exp_year,card_cvv,billing_first_name,billing_last_name,billing_address,billing_city,billing_state,billing_zip',
            'card_exp_month' => 'digits:2|numeric|sometimes|nullable',
            'card_exp_year' => 'digits:4|integer|sometimes|nullable',
            'card_cvv' => 'numeric|nullable|sometimes',
            'billing_first_name' => 'max:50|nullable|sometimes|required_with:card_number,card_exp_month,card_exp_year,card_cvv,billing_last_name,billing_address,billing_city,billing_state,billing_zip',
            'billing_last_name' => 'max:50|nullable|sometimes|required_with:card_number,card_exp_month,card_exp_year,card_cvv,billing_first_name,billing_address,billing_city,billing_state,billing_zip',
            'billing_company' => 'max:50|nullable',
            'billing_address' => 'max:60|nullable|sometimes|required_with:card_number,card_exp_month,card_exp_year,card_cvv,billing_first_name,billing_last_name,billing_city,billing_state,billing_zip',
            'billing_city' => 'max:40|nullable|sometimes|required_with:card_number,card_exp_month,card_exp_year,card_cvv,billing_first_name,billing_last_name,billing_address,billing_state,billing_zip',
            'billing_state' => 'max:40|nullable|sometimes|required_with:card_number,card_exp_month,card_exp_year,card_cvv,billing_first_name,billing_last_name,billing_address,billing_city,billing_zip',
            'billing_zip' => 'max:20|nullable|sometimes|required_with:card_number,card_exp_month,card_exp_year,card_cvv,billing_first_name,billing_last_name,billing_address,billing_city,billing_state',
            'billing_country' => 'max:60|nullable',
            'billing_phone_number' => 'max:25|nullable',
            'billing_fax_number' => 'max:25|nullable',
            'same_as_billing' => 'nullable|required_without_all:shipping_first_name,shipping_last_name,shipping_address,shipping_city,shipping_state,shipping_zip,',
            'shipping_first_name' => 'max:50|nullable|required_unless:same_as_billing,true',
            'shipping_last_name' => 'max:50|nullable|required_unless:same_as_billing,true',
            'shipping_company' => 'max:50|nullable',
            'shipping_address' => 'max:60|nullable|required_unless:same_as_billing,true',
            'shipping_city' => 'max:40|nullable|required_unless:same_as_billing,true',
            'shipping_state' => 'max:40|nullable|required_unless:same_as_billing,true',
            'shipping_zip' => 'max:20|nullable|required_unless:same_as_billing,true',
            'shipping_country' => 'max:60|nullable',
            'shipping_phone_number' => 'max:25|nullable',
            'shipping_fax_number' => 'max:25|nullable',
        ]);

        $customerExists = Customer::where('email', $request->email)->where('gateway', $setting->gateway)->first();

        if ($customerExists) {
            session()->flash('error', 'Customer already exists with email: ' . $request->email . ' on gateway: ' . $customerExists->gateway);
            
            return back()->withInput();
        } else {
            $customerExists = false;
        }

        //Check if email has been used
        //Create customer on shopify
        //Check if customer has been created
        //Create authorize.net profile

        $data = array(
            'query' => $request->email
        );
        $customerEmail = $store->api()->rest('GET', '/admin/customers/search.json', $data);

        if (empty($customerEmail->body->customers)) {
            session()->flash('error', 'Email does not match a customer in shopify');

            return back()->withInput();
        }


        // $customer = new Customer();
        // $card = new Card();
        // $lastFour = substr($request->card_number, -4);
        
        $exp = $request->card_exp_year . '-' . $request->card_exp_month;

        

        // Common setup for API credentials
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName($setting->authorize_payment_api_login_id);
        $merchantAuthentication->setTransactionKey($setting->authorize_payment_transaction_key);

        $refId = 'ref'.time();

        // Create a Customer Profile Request
        //  1. (Optionally) create a Payment Profile
        //  2. (Optionally) create a Shipping Profile
        //  3. Create a Customer Profile (or specify an existing profile)
        //  4. Submit a CreateCustomerProfile Request
        //  5. Validate Profile ID returned

        // Set credit card information for payment profile
        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber($request->card_number);
        $creditCard->setExpirationDate($exp);
        $creditCard->setCardCode($request->card_cvv);
        $paymentCreditCard = new AnetAPI\PaymentType();
        $paymentCreditCard->setCreditCard($creditCard);

        // Create the Bill To info for new payment type
        $billTo = new AnetAPI\CustomerAddressType();
        $billTo->setFirstName($request->billing_first_name);
        $billTo->setLastName($request->billing_last_name);
        $billTo->setCompany($request->billing_company);
        $billTo->setAddress($request->billing_address);
        $billTo->setCity($request->billing_city);
        $billTo->setState($request->billing_state);
        $billTo->setZip($request->billing_zip);
        $billTo->setCountry($request->billing_country);
        $billTo->setPhoneNumber($request->billing_phone_number);
        $billTo->setfaxNumber($request->billing_fax_number);


        if ($request->same_as_billing == 'true') {
            // Create a customer shipping address from billing
            $customerShippingAddress = new AnetAPI\CustomerAddressType();
            $customerShippingAddress->setFirstName($request->billing_first_name);
            $customerShippingAddress->setLastName($request->billing_last_name);
            $customerShippingAddress->setCompany($request->billing_company);
            $customerShippingAddress->setAddress($request->billing_address);
            $customerShippingAddress->setCity($request->billing_city);
            $customerShippingAddress->setState($request->billing_state);
            $customerShippingAddress->setZip($request->billing_zip);
            $customerShippingAddress->setCountry($request->billing_country);
            $customerShippingAddress->setPhoneNumber($request->billing_phone_number);
            $customerShippingAddress->setFaxNumber($request->billing_fax_number);
    
            // Create an array of any shipping addresses
            $shippingProfiles[] = $customerShippingAddress;   
        } else {
             // Create a customer shipping address
            $customerShippingAddress = new AnetAPI\CustomerAddressType();
            $customerShippingAddress->setFirstName($request->shipping_first_name);
            $customerShippingAddress->setLastName($request->shipping_last_name);
            $customerShippingAddress->setCompany($request->shipping_company);
            $customerShippingAddress->setAddress($request->shipping_address);
            $customerShippingAddress->setCity($request->shipping_city);
            $customerShippingAddress->setState($request->shipping_state);
            $customerShippingAddress->setZip($request->shipping_zip);
            $customerShippingAddress->setCountry($request->shipping_country);
            $customerShippingAddress->setPhoneNumber($request->shipping_phone_number);
            $customerShippingAddress->setFaxNumber($request->shipping_fax_number);
    
            // Create an array of any shipping addresses
            $shippingProfiles[] = $customerShippingAddress;
        }

        // Create a new CustomerPaymentProfile object
        $paymentProfile = new AnetAPI\CustomerPaymentProfileType();
        $paymentProfile->setCustomerType($request->customer_type);
        $paymentProfile->setBillTo($billTo);
        $paymentProfile->setPayment($paymentCreditCard);
        $paymentProfiles[] = $paymentProfile;


        // Create a new CustomerProfileType and add the payment profile object
        $customerProfile = new AnetAPI\CustomerProfileType();
        $customerProfile->setDescription($request->description);
        // $customerProfile->setMerchantCustomerId("M_" . time());
        $customerProfile->setEmail($request->email);
        $customerProfile->setpaymentProfiles($paymentProfiles);
        
        if (empty($request->same_as_billing) || $request->same_as_billing == 'true') {
            $customerProfile->setShipToList($shippingProfiles);
        }
        

        // Assemble the complete transaction request
        $requestAnet = new AnetAPI\CreateCustomerProfileRequest();
        $requestAnet->setMerchantAuthentication($merchantAuthentication);
        $requestAnet->setRefId($refId);
        $requestAnet->setProfile($customerProfile);

        // Create the controller and get the response
        $controller = new AnetController\CreateCustomerProfileController($requestAnet);
        if (env('AUTHORIZE_ENV', '') == 'SANDBOX') {
            $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
        } else {
            $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
        }
        
        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
            // echo "Succesfully created customer profile : " . $response->getCustomerProfileId() . "\n";
            $paymentProfiles = $response->getCustomerPaymentProfileIdList();
            // echo "SUCCESS: PAYMENT PROFILE ID : " . $paymentProfiles[0] . "\n";

            $customer = Customer::where('email', $request->email)->first();

            Log::debug('Customer Lookup if good response ' . print_r($response, true));

            if (!$customer && !$customerExists) {
                $customer = new Customer();
                $customer->email = $request->email;
                
                Log::debug('Customer not found so new instance created ' . print_r($customer, true));
            }

            if( empty($customer->shopify_customer_id)) {
                $data = array(
                    'query' => $request->email
                );

                $customerEmail = $store->api()->rest('GET', '/admin/customers/search.json', $data);

                if (empty($customerEmail->body->customers)) {
                    session()->flash('error', 'Email does not match a customer in shopify');

                    return back()->withInput();
                }

                $shopifyMatch = $customerEmail->body->customers[0];
                if (isset($shopifyMatch)) {
                    $customer->shopify_customer_id = $shopifyMatch->id;

                    Log::debug('Customer matched ' . print_r($shopifyMatch, true));
                }
                
               
            }

            $customer->first_name = $request->billing_first_name;
            $customer->last_name = $request->billing_last_name;
            $customer->customer_type = $request->customer_type;
            $customer->employee_name = $request->employee_name;
            $customer->employee_email = $request->employee_email;

            $customerGatewayId = $response->getCustomerProfileId();

            Log::debug('Customer gateway ID ' . print_r($customerGatewayId, true));
            
            $responseCustomer = app('App\Http\Controllers\OrderController')->getCustomerProfile($customer, $setting, $customerGatewayId);
            
            if ($responseCustomer['error']) {
                $errorCode = $responseCustomer['response']['messages'][0]->getCode();
                $errorText = $responseCustomer['response']['messages'][0]->getText();

                session()->flash('error', $errorText . ' |  Error Code: ' . $errorCode);

                return back()->withInput();
            }

            Log::debug('Customer looked up on authorize.net for profile ' . print_r($responseCustomer, true));

            if ( ($responseCustomer['response'] != null) && ($responseCustomer['error'] == "") ) {
                $profile = $responseCustomer['profile'];

                Log::debug('Customer on authorize found ' . print_r($profile, true));
                
                if (empty($customer->shop_id)) {
                    $customer->shop_id = $store->id;
                }
                    
                if (empty($customer->gateway_profile_id)) {
                    $customer->gateway_profile_id = $profile['cust_id'];
                    $customer->gateway = $setting->gateway;
                }
                
                $saveCustomer = $customer->save();

                Log::debug('Saving Customer... ' . print_r($saveCustomer, true));
                
                $newCustomer = Customer::where('email', $request->email)->first();

                Log::debug('Finding new Customer that was saved ' . print_r($newCustomer, true));

                $cards = $responseCustomer['cards'];

                foreach ($cards as $card) {
                    $findCard = Card::where('payment_profile_id', $card->paymentProfileId)->first();

                    if (!$findCard) {
                        $newCard = new Card();
                        $newCard->customer_id = $newCustomer->id;
                        $newCard->first_name = $card->cardFirst;
                        $newCard->last_name = $card->cardLast;
                        $newCard->card_number = $card->cardNumber;
                        $newCard->card_exp = $card->cardExp;
                        $newCard->card_type = $card->cardType;
                        $newCard->payment_profile_id = $card->paymentProfileId;
                        $newCard->shopify_customer_id = $newCustomer->shopify_customer_id;
                        $newCard->gateway_customer_profile_id = $profile['cust_id'];
                        $newCard->gateway = $setting->gateway;

                        $saveCard = $newCard->save();

                        Log::debug('Saving Card... ' . print_r($saveCard, true));
                    }
                }
            
            }
           
            session()->flash('success', 'Success! A new customer profile was created.');

            Log::debug('Success Flashed and Redirected to /customer');

            return view('/customer-saved');
        } else {
            session()->flash('error', 'Whoops! Something went wrong when trying to create a customer profile.');

            return view('error-customer-create', compact('response'));
        }
        
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function show(Customer $customer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function edit(Customer $customer)
    {   
        $customer;
        $store = ShopifyApp::shop();
            
        $setting = Setting::where('shop_id', $store->id)->first();

        if (empty($setting)) {
            \Session::flash('error', 'Please setup your payment gateway before you can charge a card on file');
            return redirect('/settings');
        }

        if (empty($customer->gateway_profile_id)) {
            \Session::flash('info', 'Please add customer to your payment gateway so you can charge their card on file');

            $cards = [];
            $card = null;
            $profile = null;

            return view('/customer-edit', compact('customer', 'profile', 'cards', 'card'));
        }
        
        $response = app('App\Http\Controllers\OrderController')->getCustomerProfile($customer, $setting);
        
        
        if ($response['error']) {
            $errorCode = $response['response']['messages'][0]->getCode();
            $errorText = $response['response']['messages'][0]->getText();

            session()->flash('error', $errorText . ' |  Error Code: ' . $errorCode);

            return back()->withInput();
        }

        if ( ($response['response'] != null) && ($response['response']->getMessages()->getResultCode() == "Ok") ) {
            $profile = $response['profile'];
            
            if (empty($customer->gateway_profile_id)) {
                $customer->gateway_profile_id = $profile['cust_id'];
                $customer->gateway = $setting->gateway;
                $customer->save();
            }
            
            $cards = $response['cards'];

            foreach ($cards as $card) {
                $findCard = Card::where('payment_profile_id', $card->paymentProfileId)->first();

                if (!$findCard) {
                    $newCard = new Card();
                    $newCard->customer_id = $customer->id;
                    $newCard->first_name = $card->cardFirst;
                    $newCard->last_name = $card->cardLast;
                    $newCard->card_number = $card->cardNumber;
                    $newCard->card_exp = $card->cardExp;
                    $newCard->card_type = $card->cardType;
                    $newCard->payment_profile_id = $card->paymentProfileId;
                    $newCard->shopify_customer_id = $customer->shopify_customer_id;
                    $newCard->gateway_customer_profile_id = $profile['cust_id'];
                    $newCard->gateway = $setting->gateway;

                    $newCard->save();
                }
            }

            $cards = Card::where('customer_id', $customer->id)->get();
            
            return view('/customer-edit', compact('customer', 'profile', 'cards'));
        
        } else {
            $cards = null;

            return view('/customer-edit', compact('customer', 'profile', 'cards', 'card'));
        }

        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Customer $customer)
    {
        $customer->employee_name = $request->employee_name;
        $customer->employee_email = $request->employee_email;
        $save = $customer->save();

        if ($save) {
            session()->flash('success', 'Customer Profile Updated!');
            return redirect('customer/'. $customer->id .'/edit');
        } else {
            session()->flash('error', 'Something went wrong. Try again later.');
            return back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Customer $customer)
    {
        //
    }
}
