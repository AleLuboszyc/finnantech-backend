<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User; // Importamos el modelo User

class SaldosInicialesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Obtenemos todos los usuarios de la base de datos.
        $users = User::all();

        // 2. Recorremos cada uno de los usuarios.
        foreach ($users as $user) {

            // 3. Verificamos si este usuario YA TIENE un saldo en ARS.
            //    Usamos first() para ver si encontramos al menos uno.
            $tieneSaldoArs = $user->saldos()->where('moneda', 'ARS')->first();

            // 4. Si NO tiene saldo en ARS (! significa "no"), entonces se lo creamos.
            if (!$tieneSaldoArs) {
                $user->saldos()->create([
                    'moneda' => 'ARS',
                    'cantidad' => 0.00
                ]);
            }
        }
    }
}

