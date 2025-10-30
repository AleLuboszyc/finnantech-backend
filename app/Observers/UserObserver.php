<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    
    public function created(User $user): void
    {
        //Justo después de que el usuario ha sido creado,
        //le creamos su primer saldo en ARS.
        $user->saldos()->create([
            'moneda' => 'ARS',
            'cantidad' => 0.00
        ]);
    }

    
    public function updated(User $user): void
    {
        
    }

   
    public function deleted(User $user): void
    {
        
    }

   
    public function restored(User $user): void
    {
        
    }

    
    public function forceDeleted(User $user): void
    {
        
    }
}
