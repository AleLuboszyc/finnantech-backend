<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; 
use Illuminate\Support\Facades\Cache; 

class CryptoController extends Controller
{
 
    public function getMarketData()
    {
        //Definimos una clave única para nuestra caché de datos de mercado.
        $cacheKey = 'market_data';

        //Usamos la función de caché de Laravel para  almacenar los datos durante 60 segundos.
        
        $marketData = Cache::remember($cacheKey, 60, function () {
            
            //Aca utilizamos laa URL base de la API de CoinGecko
            $url = 'https://api.coingecko.com/api/v3/coins/markets';

            try {
                // Hacemos la llamada a la API usando el cliente HTTP de Laravel.
                // Pedimos las 10 criptomonedas con mayor capitalización de mercado, en dólares (usd).
                $response = Http::get($url, [
                    'vs_currency' => 'usd',
                    'order' => 'market_cap_desc',
                    'per_page' => 10,
                    'page' => 1,
                    'sparkline' => 'false'
                ]);

                //Si la llamada fue exitosa, devolvemos los datos JSON.
                if ($response->successful()) {
                    return $response->json();
                }

                //Si CoinGecko falla, devolvemos null para no cachear un error.
                return null;

            } catch (\Exception $e) {
               
                return null;
            }
        });

        //Verificamos si los datos se obtuvieron correctamente
        if ($marketData) {
            return response()->json($marketData);
        }

        //Si después de todo el proceso, los datos son nulos, devolvemos un error.
        return response()->json(['error' => 'No se pudo obtener la información del mercado en este momento.'], 503); // 503 Service Unavailable
    }
}
