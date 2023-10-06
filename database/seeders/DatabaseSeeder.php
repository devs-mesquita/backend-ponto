<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $tecnologia = \App\Models\Setor::firstOrCreate([
            'id' => 1
            'nome' => 'TECNOLOGIA',
            'soma_entrada' => -1,
            'soma_saida' => 1,
        ]);

        $tecnologia = \App\Models\Setor::firstOrCreate([
            'id' => 2,
            'nome' => 'PONTO',
            'soma_entrada' => 0,
            'soma_saida' => 0,
        ]);

        \App\Models\User::firstOrcreate([
            'name' => 'Felipe Vidal',
            'email' => 'felipe.vidal@mesquita.rj.gov.br',
            'password' => Hash::make(config('app.user_default_password', '')),
            'cpf'      => $request->cpf,
            'nivel'    => "Super-Admin",
            'setor_id' => $tecnologia->id,
        ]);
    }
}
