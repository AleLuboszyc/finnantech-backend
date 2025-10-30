<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; 
use App\Models\Saldo; 

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    
   
    protected $fillable = [
        'nombre', 
        'apellido',
        'email',
        'password',
        'dni',
        'fecha_nacimiento',
        'telefono',
        'sexo',
        'avatar_url', 
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
      
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'fecha_nacimiento' => 'date', 
        ];
    }

   
    public function saldos()
    {
        return $this->hasMany(Saldo::class);
    }

    public function transacciones()
    {
        return $this->hasMany(Transaccion::class);
    }
}