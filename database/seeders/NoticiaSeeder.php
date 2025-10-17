<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class NoticiaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('noticias')->insert([
            [
                'title' => 'Bitcoin supera la barrera de los $50,000 impulsado por inversores institucionales',
                'content' => 'El precio de Bitcoin ha experimentado un notable aumento esta semana, superando la resistencia clave de los 50,000 dólares. Analistas atribuyen este movimiento a la creciente adopción por parte de grandes fondos de inversión...',
                'source' => 'CoinDesk',
                'image_url' => 'https://placehold.co/600x400/F7931A/FFFFFF?text=BTC',
                'published_at' => Carbon::now()->subDays(1),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Ethereum 2.0: La actualización "Merge" se completará con éxito',
                'content' => 'La tan esperada actualización "The Merge" de la red Ethereum ha sido finalizada, marcando la transición de Proof-of-Work a Proof-of-Stake. Se espera que esto reduzca el consumo energético de la red en más de un 99%...',
                'source' => 'Bloomberg Crypto',
                'image_url' => 'https://placehold.co/600x400/627EEA/FFFFFF?text=ETH',
                'published_at' => Carbon::now()->subDays(2),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Regulación Cripto: El G7 acuerda un marco común para activos digitales',
                'content' => 'Los líderes financieros del G7 han anunciado un acuerdo histórico para establecer un marco regulatorio común para los activos digitales. El objetivo es fomentar la innovación mientras se protege a los consumidores y se previene el lavado de dinero...',
                'source' => 'Reuters',
                'image_url' => 'https://placehold.co/600x400/718096/FFFFFF?text=G7',
                'published_at' => Carbon::now()->subDays(3),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}