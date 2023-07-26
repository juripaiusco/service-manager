<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $guarded = [];

    use HasFactory;

    public function customersServicesDetails()
    {
        return $this->hasMany(CustomerServiceDetail::class, 'service_id', 'id');
    }
}
