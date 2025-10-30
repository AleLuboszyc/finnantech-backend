<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Noticia extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content', 
        'source',
        'image_url',
        'published_at'
    ];

    protected $casts = [
        'published_at' => 'datetime'
    ];
}