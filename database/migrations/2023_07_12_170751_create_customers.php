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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->string('company');
            $table->string('piva');
            $table->string('cf');
            $table->string('address');
            $table->string('city');
            $table->string('cap');
            $table->string('name');
            $table->string('cellphone');
            $table->string('telephone');
            $table->string('email');
            $table->longText('note');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
