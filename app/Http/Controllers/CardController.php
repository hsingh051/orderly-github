<?php

namespace App\Http\Controllers;

use App\Card;
use App\Customer;
use App\Setting;
use App\Http\Controllers\OrderController;
use Illuminate\Http\Request;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
use OhMyBrew\ShopifyApp\Facades\ShopifyApp;
use Log;

class CardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $customerId = $request->id;
        return view('card-create', compact('customerId'));
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

        $customer = Customer::find($request->id);

        $validatedData = $request->validate([
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
        ]);

        // Common setup for API credentials
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName($setting->authorize_payment_api_login_id);
        $merchantAuthentication->setTransactionKey($setting->authorize_payment_transaction_key);
        
        // Set the transaction's refId
        $refId = 'ref' . time();

        $exp = $request->card_exp_year . '-' . $request->card_exp_month;
        
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

        // Create a new Customer Payment Profile object
        $paymentProfile = new AnetAPI\CustomerPaymentProfileType();
        $paymentProfile->setCustomerType($request->customer_type);
        $paymentProfile->setBillTo($billTo);
        $paymentProfile->setPayment($paymentCreditCard);
        $paymentProfile->setDefaultPaymentProfile(true);

        // Assemble the complete transaction request
        $paymentprofilerequest = new AnetAPI\CreateCustomerPaymentProfileRequest();
        $paymentprofilerequest->setMerchantAuthentication($merchantAuthentication);

        // Add an existing profile id to the request
        $paymentprofilerequest->setCustomerProfileId($customer->gateway_profile_id);
        $paymentprofilerequest->setPaymentProfile($paymentProfile);
        $paymentprofilerequest->setValidationMode("liveMode");

        // dd($paymentprofilerequest, $customer, $request, $request->id, $setting->authorize_payment_api_login_id, $setting->authorize_payment_transaction_key);

        // Create the controller and get the response
        $controller = new AnetController\CreateCustomerPaymentProfileController($paymentprofilerequest);
        if (env('AUTHORIZE_ENV', '') == 'SANDBOX') {
            $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
        } else {
            $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
        }

        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok") ) {
            // echo "Create Customer Payment Profile SUCCESS: " . $response->getCustomerPaymentProfileId() . "\n";

            //Save Card
            $newCard = new Card();
            $newCard->customer_id = $customer->id;
            $newCard->first_name = $customer->first_name;
            $newCard->last_name = $customer->last_name;
            $newCard->card_number = substr($request->card_number, -4);;
            $newCard->card_exp = $exp;
            $newCard->payment_profile_id = $response->getCustomerPaymentProfileId();
            $newCard->gateway_customer_profile_id = $customer->gateway_profile_id;
            $newCard->gateway = $setting->gateway;

            $saveCard = $newCard->save();

            Log::debug('Saving Card... ' . print_r($saveCard, true));

            session()->flash('success', 'Card Added');

            return view('/card-saved', compact('customer'));
        } else {
            // echo "Create Customer Payment Profile: ERROR Invalid response\n";
            $errorMessages = $response->getMessages()->getMessage();
            // echo "Response : " . $errorMessages[0]->getCode() . "  " .$errorMessages[0]->getText() . "\n";

            dd($response);
            session()->flash('error','Error Code: ' . $errorMessages[0]->getCode() . ' | Error Text: ' . $errorMessages[0]->getText() );

            return back()->withInput();

        }
        return $response;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Card  $card
     * @return \Illuminate\Http\Response
     */
    public function show(Card $card)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Card  $card
     * @return \Illuminate\Http\Response
     */
    public function edit(Card $card)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Card  $card
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Card $card)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Card  $card
     * @return \Illuminate\Http\Response
     */
    public function destroy(Card $card)
    {
        $store = ShopifyApp::shop();
        
        $setting = Setting::where('shop_id', $store->id)->first();

        $customer = Customer::find($card->customer_id);
        

        // Common setup for API credentials
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName($setting->authorize_payment_api_login_id);
        $merchantAuthentication->setTransactionKey($setting->authorize_payment_transaction_key);
        
        // Set the transaction's refId
        $refId = 'ref' . time();

        // Use an existing payment profile ID for this Merchant name and Transaction key
	  
        $request = new AnetAPI\DeleteCustomerPaymentProfileRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setCustomerProfileId($card->gateway_customer_profile_id);
        $request->setCustomerPaymentProfileId($card->payment_profile_id);

        $controller = new AnetController\DeleteCustomerPaymentProfileController($request);
        if (env('AUTHORIZE_ENV', '') == 'SANDBOX') {
            $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
        } else {
            $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
        }


        if (($response != null) && ($response->getMessages()->getResultCode() == "Ok") )
        {

            $card->delete();

            session()->flash('success', 'Card Deleted!');

            return back();
        }
        else
        {
            // echo "ERROR :  Delete Customer Payment Profile: Invalid response\n";
            $errorMessages = $response->getMessages()->getMessage();
            // echo "Response : " . $errorMessages[0]->getCode() . "  " .$errorMessages[0]->getText() . "\n";

            session()->flash('error', 'Error Code: ' . $errorMessages[0]->getCode() . ' | Error Text: ' . $errorMessages[0]->getText() );

            return back()->withInput();
        }
        return $response;
    }
}
