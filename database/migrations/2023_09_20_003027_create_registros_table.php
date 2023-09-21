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
        Schema::create('registros', function (Blueprint $table) {
            $table->id();

            // $table->string('nome');
            $table->string('cpf',14);

            $table->date('data');

            $table->time('entrada');
            $table->string('entrada_img',200);
            
            $table->time('entrada_alm')             ->nullable();
            $table->string('entrada_alm_img',200)   ->nullable();
            
            $table->time('volta_alm')               ->nullable();
            $table->string('volta_alm_img',200)     ->nullable();

            $table->time('saida')                   ->nullable();
            $table->string('saida_img',200)         ->nullable();
            
            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registros');
    }
};
