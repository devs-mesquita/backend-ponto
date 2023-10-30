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
        $terminal = \App\Models\Setor::firstOrCreate(["nome" => "TERMINAL"], [
            "nome" => "TERMINAL",
            "soma_entrada" => 0,
            "soma_saida" => 0,
        ]);

        \App\Models\User::firstOrCreate(["email" => "root@mesquita.rj.gov.br"], [
            "name" => "Root User",
            "email" => "root@mesquita.rj.gov.br",
            "cpf"      => "sistema",
            "nivel"    => "Super-Admin",
            "password" => Hash::make(config("app.user_default_password", "")),
            "setor_id" => $terminal->id,
        ]);
    }
}
