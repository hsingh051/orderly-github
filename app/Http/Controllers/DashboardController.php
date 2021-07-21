<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OhMyBrew\ShopifyApp\Facades\ShopifyApp;
use Log;

use App\Order;

use OhMyBrew\ShopifyApp\Services\WebhookManager;

class DashboardController extends Controller
{
    public function index() 
    {
        $orders = Order::where('order_id', '!=', 'null')->orderBy('created_at', 'desc')->paginate(25);

        $order = Order::where('order_id', '!=', 'null')->last();

        $shop = ShopifyApp::shop();

        return view('order', compact('orders', 'shop', 'order'));
    }
}
