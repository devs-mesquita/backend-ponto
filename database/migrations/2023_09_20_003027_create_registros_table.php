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
            
            $table->string('cpf', 11);
            $table->foreign('cpf')->references('cpf')->on('users');

            $table->enum('tipo',['entrada','inicio-intervalo','fim-intervalo', 'saida', 'abono', 'ferias', 'falta', 'feriado', 'facultativo']);
            $table->string('img', 200);
            $table->dateTime('data_hora');
            $table->timestamps();

            $table->BigInteger('creator_id')->unsigned();
            $table->foreign('creator_id')->references('id')->on('users');
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
