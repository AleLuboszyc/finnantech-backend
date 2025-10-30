<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Noticia; 
use Illuminate\Http\Request;

class NoticiaController extends Controller
{
    public function index()
    {
       
        $noticias = Noticia::orderBy('published_at', 'desc')->get();
        return response()->json($noticias);
    }
}