<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; 
use App\Models\Saldo; // Esto ya lo tenías, ¡perfecto!

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    
    // -----------------------------------------------------------------
    //  👇 AQUÍ ESTÁ EL CAMBIO MÁS IMPORTANTE 👇
    // -----------------------------------------------------------------
    // Reemplazamos 'name' y agregamos todos los campos nuevos.
    protected $fillable = [
        'nombre', // Cambiado de 'name'
        'apellido',
        'email',
        'password',
        'dni',
        'fecha_nacimiento',
        'telefono',
        'sexo',
        'avatar_url', // Lo agregamos de una vez para el Paso 2 (foto de perfil)
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
        // -----------------------------------------------------------------
        //  👇 AQUÍ AÑADIMOS EL CAST PARA LA FECHA 👇
        // -----------------------------------------------------------------
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'fecha_nacimiento' => 'date', // ¡Añadido!
        ];
    }

    /**
     * Define la relación "uno a muchos" con los saldos.
     * Esta función ya la tenías y estaba perfecta.
     */
    public function saldos()
    {
        return $this->hasMany(Saldo::class);
    }
}