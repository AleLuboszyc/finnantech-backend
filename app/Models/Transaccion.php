<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaccion extends Model
{
    use HasFactory;
    protected $table = 'transacciones';
    protected $fillable = [
        'user_id',
        'tipo',
        'moneda_origen',
        'cantidad_origen',
        'moneda_destino',
        'cantidad_destino',
        'precio_unitario',
    ];

    // Relación: Una transacción pertenece a un usuario
    public function user() {
        return $this->belongsTo(User::class);
    }
}