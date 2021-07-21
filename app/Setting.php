<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'gateway',
        'shop_id',
        'authorize_payment_api_login_id',
        'authorize_payment_transaction_key',
        'authorize_key',
        'stripe_published_key',
        'stripe_secret_key',
    ];

    /**
     * The Shop that belong to the orders.
     */
    public function shop()
    {
        return $this->belongsTo('OhMyBrew\ShopifyApp\Models\Shop');
    }

}
