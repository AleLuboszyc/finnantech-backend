<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Noticia; // Asegurarse de tener el modelo Noticia
use Illuminate\Http\Request;

class NoticiaController extends Controller
{
    public function index()
    {
        // Obtenemos todas las noticias, ordenadas por la más reciente primero
        $noticias = Noticia::orderBy('published_at', 'desc')->get();
        return response()->json($noticias);
    }
}