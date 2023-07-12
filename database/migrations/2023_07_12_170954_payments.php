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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->string('sid');
            $table->integer('customer_service_id')->index();
            $table->timestamp('customer_service_expiration')->nullable();
            $table->string('type');
            $table->timestamp('payment_date')->nullable();
            $table->float('amount');
            $table->longText('services');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
