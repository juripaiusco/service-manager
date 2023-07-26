<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    /*public function service()
    {
        return $this->hasMany(Service::class, 'customer_id', 'id');
    }*/

    public function servicesDetails()
    {
        return $this->hasMany(CustomerServiceDetail::class, 'customer_id', 'id');
    }
}
