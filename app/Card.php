<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cards';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'customer_id',
        'first_name',
        'last_name',
        'card_number',
        'card_exp',
        'card_type',
        'shopify_customer_id',
        'payment_profile_id',
        'gateway_customer_profile_id',
        'gateway',
        'validated',
        'validated_exp',
        'validated_at',
    ];

    /**
     * The Card belongs to a customer.
     */
    public function cards()
    {
        return $this->belongsTo('App\Customer');
    }

    /**
     * The card has an order.
     */
    public function order()
    {
        return $this->belongsTo('App\Order');
    }
}
