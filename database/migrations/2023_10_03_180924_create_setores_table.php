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
        Schema::create('setores', function (Blueprint $table) {
            $table->id();
            
            $table->string('nome');
            $table->string('cnpj')->nullable();
            $table->string('cnae')->nullable();
            $table->string('empresa')->nullable();
            $table->string('visto_fiscal')->nullable();

            $table->string('logradouro')->nullable();
            $table->string('numero_logradouro')->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->string('uf')->nullable();
            $table->string('cep')->nullable();

            $table->integer('soma_entrada')->nullable();
            $table->integer('soma_saida')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setores');
    }
};
