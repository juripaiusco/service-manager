<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerService extends Model
{
    use HasFactory;

    protected $table = 'customers_services';

    public function customer()
    {
        return $this->hasOne(Customer::class, 'id', 'customer_id');
    }

    public function details()
    {
        return $this->hasMany(CustomerServiceDetail::class, 'customer_service_id', 'id');
    }
}
