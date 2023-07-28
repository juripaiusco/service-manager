<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerService extends Model
{
    protected $guarded = [];

    use HasFactory;

    protected $table = 'customers_services';

    public function customer()
    {
        return $this->hasOne(Customer::class, 'id', 'customer_id');
    }

    public function details()
    {
        return $this->hasMany(CustomerServiceDetail::class, 'customer_service_id', 'id')
            ->withSum('service', 'price_buy');
    }

    public function detailsService()
    {
        return $this->hasManyThrough(
            Service::class,
            CustomerServiceDetail::class,
            'customer_service_id',
            'id',
            'id',
            'service_id'
        );
    }
}
