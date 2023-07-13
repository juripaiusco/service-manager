<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerServiceDetail extends Model
{
    use HasFactory;

    protected $table = 'customers_services_details';

    public function service()
    {
        return $this->hasOne(Service::class, 'id', 'service_id');
    }

}
