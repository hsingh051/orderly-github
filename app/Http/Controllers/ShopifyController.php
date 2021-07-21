<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Log;

class ShopifyController extends Controller
{
    public function draftOrdersCreate(Request $request)
    {
        Log::info('draft order created ' . $request->body);

        return response()
            ->view('hello', $data, 200)
            ->header('Content-Type', $type);
    }
}
