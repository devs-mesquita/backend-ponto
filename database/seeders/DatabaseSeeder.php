<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $ponto = \App\Models\Setor::firstOrCreate(["nome" => "PONTO"], [
            "nome" => "PONTO",
            "soma_entrada" => 0,
            "soma_saida" => 0,
        ]);

        // $tecnologia = \App\Models\Setor::firstOrCreate([ "nome" => "TECNOLOGIA" ], [
        //     "nome" => "TECNOLOGIA",
        //     "soma_entrada" => -1,
        //     "soma_saida" => 1,
        // ]);

        \App\Models\User::firstOrCreate(["email" => "root@mesquita.rj.gov.br"], [
            "email" => "root@mesquita.rj.gov.br",
            "name" => "Super Admin",
            "password" => Hash::make(config("app.user_default_password", "")),
            "cpf"      => "11111111111",
            "nivel"    => "Super-Admin",
            "setor_id" => $ponto->id,
        ]);
    }
}
