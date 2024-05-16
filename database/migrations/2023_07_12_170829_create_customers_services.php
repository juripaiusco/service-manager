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
        Schema::create('customers_services', function (Blueprint $table) {
            $table->id();

            $table->integer('customer_id')->index();
            $table->string('name');
            $table->string('reference')->nullable();
            $table->timestamp('expiration')->nullable();
            $table->string('expiration_monthly', 1)->nullable();
            $table->string('autorenew', 1)->nullable();
            $table->string('no_email_alert', 1)->nullable();

            $table->string('customer_company')->nullable();
            $table->string('customer_piva')->nullable();
            $table->string('customer_cf')->nullable();
            $table->string('customer_address')->nullable();
            $table->string('customer_city')->nullable();
            $table->string('customer_cap')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_cellphone')->nullable();
            $table->string('customer_telephone')->nullable();
            $table->string('customer_email')->nullable();
            $table->longText('customer_note')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers_services');
    }
};
