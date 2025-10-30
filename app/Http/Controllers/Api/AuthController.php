<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
 
   
    public function register(Request $request)
    {
        //VALIDACIÓN ACTUALIZADA
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed', 
            'dni' => 'required|string|max:10|unique:users',
            'fecha_nacimiento' => 'required|date',
            'telefono' => 'required|string|max:20',
            'sexo' => 'required|string|in:masculino,femenino,otro', 
            'terms' => 'accepted',
        ]);

        //CREACIÓN DE USUARIO ACTUALIZADA
        $user = User::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'email' => $request->email,
            'password' => Hash::make($request->password), 
            'dni' => $request->dni,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'telefono' => $request->telefono,
            'sexo' => $request->sexo,
        ]);

        //Devolver una respuesta crear un token.
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Usuario registrado exitosamente!',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user // Devolvemos el usuario creado
        ], 201);
    }

   
    public function login(Request $request)
    {
        //Validar los datos para el login
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        //Buscar al usuario por su email
        $user = User::where('email', $request->email)->first();

        //Verificar si el usuario existe y la contraseña es correcta
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        //Crea y devuelve un token de autenticación
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user // Devuelve al usuario en el login también es útil esta funcion al proyecto
        ]);
    }

  
    public function profile(Request $request)
    {
        // Usamos saldos para cargar también la relación con el usuario.
        $user = $request->user()->load('saldos');

        return response()->json($user);
    }

    public function uploadAvatar(Request $request)
    {
        //Validación del archivo
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', 
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //Obtener el usuario autenticado
        $user = $request->user();

        //Borrar la foto anterior si existe
        if ($user->avatar_url) {
            // Extraer el path relativo del archivo (Ejemplo: 'avatars/nombre.jpg')
            $oldPath = str_replace('/storage/', '', $user->avatar_url); 
            Storage::disk('public')->delete($oldPath);
        }

        //Guardar la nueva foto
        //El archivo se guardará 
        $path = $request->file('avatar')->store('avatars', 'public');

        //Actualizar la base de datos
        //Guardamos la URL pública 
        $user->avatar_url = Storage::url($path);
        $user->save();

        //Devuelve la respuesta con el usuario actualizado
        return response()->json([
            'message' => 'Foto de perfil actualizada exitosamente.',
            'user' => $user
        ]);
    }
}