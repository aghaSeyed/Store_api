<?php

namespace App\Shop\VerifyPhone;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use App\Shop\Customers\Customer;
use Nicolaslopezj\Searchable\SearchableTrait;
use Laravel\Passport\HasApiTokens;
class Verify extends Model
{
    use Notifiable, SearchableTrait,HasApiTokens;
    /**
     * @var array
     *
     */
    protected $fillable = [
        'phone',
        'token',
        'status',
        'customer_id',
        'attemp'
    ];
    protected $searchable = [
        'columns' => [
            'verifies.phone' => 10,
            'verifies.status' => 5
        ]
    ];

    public function isVerified(){
        return $this->fillable['status'];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
