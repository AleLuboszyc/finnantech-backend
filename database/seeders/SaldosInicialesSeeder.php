<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User; 

class SaldosInicialesSeeder extends Seeder
{
   
    public function run(): void
    {
        //Obtenemos todos los usuarios de la base de datos.
        $users = User::all();

        //Recorremos cada uno de los usuarios.
        foreach ($users as $user) {

            //Verificamos si este usuario YA TIENE un saldo en ARS.
            $tieneSaldoArs = $user->saldos()->where('moneda', 'ARS')->first();

            //Si NO tiene saldo en ARS entonces se lo creamos.
            if (!$tieneSaldoArs) {
                $user->saldos()->create([
                    'moneda' => 'ARS',
                    'cantidad' => 0.00
                ]);
            }
        }
    }
}

