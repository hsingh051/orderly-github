<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Order extends Model
{
    use Searchable;

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'shop_id' => $this->shop_id,
            'order_id' => $this->order_id,
            'card_id' => $this->card_id,
            'email' => $this->email,
            'order_name' => $this->order_name,
            'amount' => $this->amount,
            'shopify_status' => $this->shopify_status,
            'gateway_status' => $this->gateway_status,
            'gateway_transaction_id' => $this->gateway_transaction_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'fraud' => $this->fraud,
            'fraud_status' => $this->fraud
        ];
    }
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'orders';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'draft_order', 'shop_id', 'draft_id', 'draft_name', 'order', 'order_id', 'order_name', 'email', 'shopify_status', 'gateway_status', 'gateway_transaction_id', 'amount', 'customer_id', 'fraud', 'fraud_status',
    ];

    protected $casts = [
        'draft_order' => 'array',
        'fraud' => 'array'
    ];

    /**
     * The Shop that belong to the orders.
     */
    public function shop()
    {
        return $this->belongsTo('OhMyBrew\ShopifyApp\Models\Shop');
    }

    /**
     * The Customer that belong to the order.
     */
    public function customer()
    {
        return $this->belongsTo('App\Customer');
    }

    /**
     * The order has a card.
     */
    public function card()
    {
        return $this->hasOne('App\Card');
    }
}