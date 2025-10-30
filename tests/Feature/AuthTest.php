<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\User; 

class AuthTest extends TestCase
{
    //Usamos RefreshDatabase para limpiar la base de datos después de cada prueba
    use RefreshDatabase; 

    /**
     * Prueba el rechazo de registro cuando el email tiene un formato inválido.
     * (Prueba de Formato de Email)
     * @return void
     */
    public function test_registro_rechaza_email_invalido(): void
    {
        $password = 'passwordsegura123';
        
        $datosInvalidos = [
            'nombre' => 'Usuario',
            'apellido' => 'Prueba',
            'email' => 'emailinvalido.com', 
            'password' => $password,
            'password_confirmation' => $password,
            'dni' => '12345678',
            'telefono' => '1122334455',
            'fecha_nacimiento' => '1990-01-01',
            'sexo' => 'masculino',
            'terms' => true, 
        ];

        //Ejecutar la petición POST a la ruta de registro
        $response = $this->postJson('/api/register', $datosInvalidos);

        //Verificar el Status: Debe fallar la validación (código HTTP 422)
        $response->assertStatus(422); 
        
        //Verificar la BD: El usuario NO debe haberse creado
        $this->assertDatabaseMissing('users', [
            'nombre' => 'Usuario',
            'email' => 'emailinvalido.com',
        ]);

        //Verificar el Error: Debe indicar que el campo 'email' falló
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * Prueba que la contraseña se hashea correctamente durante un registro exitoso.
     * (Cubre el punto: Prueba de Hashing de Contraseña)
     * @return void
     */
    public function test_password_se_hashea_al_registrar(): void
    {
        $passwordPlana = 'MiContraseñaSegura123';
        
        //Los datos deben coincidir exactamente con las validaciones de tu AuthController
        $datosValidos = [
            'nombre' => 'Alejandro',
            'apellido' => 'Luboszyc',
            'email' => 'valido@finnantech.com',
            'password' => $passwordPlana,
            'password_confirmation' => $passwordPlana,
            'dni' => '12345678',
            'telefono' => '1122334455',
            'fecha_nacimiento' => '1990-01-01',
            'sexo' => 'masculino', 
            'terms' => true, 
        ];

        //Ejecutar el registro
        $response = $this->postJson('/api/register', $datosValidos);
        
        //Debe ser un registro exitoso (código HTTP 201 Created)
        $response->assertStatus(201); 

        //Buscar el usuario recién creado en la BD
        $usuario = User::where('email', $datosValidos['email'])->first();
        
        //Verificación Crítica: El usuario debe existir en la BD
        $this->assertNotNull($usuario);

        //Verificación del Hash: Comprobar que la contraseña plana coincide con el hash almacenado.
        $this->assertTrue(
            Hash::check($passwordPlana, $usuario->password),
            'La contraseña no fue hasheada correctamente o el hash no coincide con la versión plana.'
        );
    }
}