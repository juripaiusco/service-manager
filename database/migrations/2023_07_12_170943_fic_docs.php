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
        Schema::create('fic_docs', function (Blueprint $table) {
            $table->id();

            $table->string('fic_id')->index();
            $table->string('tipo_doc');
            $table->string('tipo');
            $table->string('numero')->nullable();
            $table->string('nome');
            $table->year('anno');
            $table->timestamp('data')->nullable();
            $table->string('categoria')->nullable();
            $table->float('importo_netto');
            $table->float('importo_iva');
            $table->float('importo_totale');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fic_docs');
    }
};
