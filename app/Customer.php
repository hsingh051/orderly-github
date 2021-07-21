<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;


class Customer extends Model
{

    //Laravel Scout Trait
    use Searchable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'customers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'shopify_customer_id',
        'card_id',
        'first_name',
        'last_name',
        'customer_type',
        'employee_name',
        'employee_email',
        'email',
        'gateway',
        'gateway_profile_id',
    ];

    /**
     * The Customer has many orders.
     */
    public function orders()
    {
        return $this->hasMany('App\Order');
    }
    
    /**
     * The Customer has many cards.
     */
    public function cards()
    {
        return $this->hasMany('App\Card');
    }

    /**
     * The Customer has one shop.
     */
    public function shop()
    {
        return $this->hasOne('App\Shop');
    }
}
