<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $guarded = [];

    use HasFactory;

    public function service()
    {
        return $this->hasMany(Service::class, 'customer_id', 'id');
    }

    public function customerService()
    {
        return $this->hasMany(CustomerService::class, 'customer_id', 'id')
            ->withSum('details', 'price_sell');
    }

    public function customerServiceDetail()
    {
        return $this->hasMany(CustomerServiceDetail::class, 'customer_id', 'id');
    }
}
