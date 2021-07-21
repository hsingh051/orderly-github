<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OhMyBrew\BasicShopifyAPI;
use OhMyBrew\ShopifyApp\Facades\ShopifyApp;
use App\Setting;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $shop = ShopifyApp::shop();
        
        $setting = Setting::findOrFail($shop->id)->shop();

        if (isset($setting)) {
            return view('/settings-edit')->with('setting', $setting);
        }
        return view('settings');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $shop = ShopifyApp::shop();

        
        return view('settings');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'authorize_payment_api_login_id' => 'required',
            'authorize_payment_transaction_key' => 'required',
        ]);

        $shop = ShopifyApp::shop();

        $setting = new Setting();
        $setting->shop_id = $shop->id;
        $setting->gateway = $request->gateway;
        $setting->authorize_payment_api_login_id = $request->authorize_payment_api_login_id;
        $setting->authorize_payment_transaction_key = $request->authorize_payment_transaction_key;
    

        $save = $setting->shop()->associate($shop);
        $save->save();
        

        if ($save) {
            \Session::flash('success', 'Settings Saved!');
            return view('settings-edit')->with('setting', $setting);
        } else {
            \Session::flash('error', 'Something went wrong! Try Again');
            return back();
        }
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        $shop = ShopifyApp::shop();

        $setting = Setting::where('shop_id', $shop->id)->first();

        return view('settings-edit')->with('setting', $setting);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'authorize_payment_api_login_id' => 'required',
            'authorize_payment_transaction_key' => 'required'
        ]);

        $shop = ShopifyApp::shop();

        $setting = Setting::findOrFail($id);
        $setting->shop_id = $shop->id;
        $setting->gateway = $request->gateway;
        $setting->authorize_payment_api_login_id = $request->authorize_payment_api_login_id;
        $setting->authorize_payment_transaction_key = $request->authorize_payment_transaction_key;

        $save = $setting->shop()->associate($shop);
        $save->save();
        

        if ($save) {
            session()->flash('success', 'Settings Saved!');
            return back()->with('setting', $setting);
        } else {
            session()->flash('error', 'Something went wrong! Try Again');
            return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
