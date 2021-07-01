<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFicDocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fic_docs', function (Blueprint $table) {
            $table->id();

            $table->char('fic_id', 255)->index();
            $table->char('tipo_doc', 255);
            $table->char('tipo', 255);
            $table->char('numero', 255)->nullable();
            $table->char('nome', 255);
            $table->year('anno');
            $table->timestamp('data')->nullable();
            $table->char('categoria', 255)->nullable();
            $table->float('importo_netto');
            $table->float('importo_iva');
            $table->float('importo_totale');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fic_docs');
    }
}
