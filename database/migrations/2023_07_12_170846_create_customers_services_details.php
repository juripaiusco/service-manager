<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers_services_details', function (Blueprint $table) {
            $table->id();

            $table->integer('customer_id')->index();
            $table->integer('service_id')->index();
            $table->integer('customer_service_id')->index();
            $table->string('reference')->nullable();
            $table->float('price_sell')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers_services_details');
    }
};
