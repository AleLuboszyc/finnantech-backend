<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
// ¡Asegúrate de que Validator esté importado si lo usas,
// pero $request->validate() no lo necesita!

class AuthController extends Controller
{
    /**
     * -----------------------------------------------------------------
     * 👇 ESTA ES LA FUNCIÓN QUE ACTUALIZAMOS 👇
     * -----------------------------------------------------------------
     * Actualizada para incluir todos los campos nuevos del formulario de React.
     */
    public function register(Request $request)
    {
        // 1. VALIDACIÓN ACTUALIZADA
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed', // 'confirmed' busca 'password_confirmation'
            'dni' => 'required|string|max:10|unique:users',
            'fecha_nacimiento' => 'required|date',
            'telefono' => 'required|string|max:20',
            'sexo' => 'required|string|in:masculino,femenino,otro', // Validar opciones
            'terms' => 'accepted', // Para el checkbox 'Acepto términos'
        ]);

        // 2. CREACIÓN DE USUARIO ACTUALIZADA
        $user = User::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'email' => $request->email,
            'password' => Hash::make($request->password), // ¡Importante! Encriptar
            'dni' => $request->dni,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'telefono' => $request->telefono,
            'sexo' => $request->sexo,
        ]);

        // 3. Devolver una respuesta (Crear un token de una vez es una buena idea)
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Usuario registrado exitosamente!',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user // Devolvemos el usuario creado
        ], 201);
    }

    /**
     * -----------------------------------------------------------------
     * Esta función está perfecta, no la toques.
     * -----------------------------------------------------------------
     */
    public function login(Request $request)
    {
        // 1. Validar los datos
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Buscar al usuario por su email
        $user = User::where('email', $request->email)->first();

        // 3. Verificar si el usuario existe y la contraseña es correcta
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        // 4. Crear y devolver un token de autenticación
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user // Devolver el usuario en el login también es útil
        ]);
    }

    /**
     * -----------------------------------------------------------------
     * Esta función también está perfecta.
     * Automáticamente devolverá el usuario con los nuevos campos
     * (nombre, apellido, etc.) gracias a que actualizamos el Modelo.
     * -----------------------------------------------------------------
     */
    public function profile(Request $request)
    {
        // Usamos with('saldos') para cargar también la relación que definimos antes.
        $user = $request->user()->load('saldos');

        return response()->json($user);
    }

    public function uploadAvatar(Request $request)
    {
        // 1. Validación del archivo
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // 2. Obtener el usuario autenticado
        $user = $request->user();

        // (Opcional) Borrar la foto anterior si existe
        if ($user->avatar_url) {
            // Extraer el path relativo del archivo (ej: 'avatars/nombre.jpg')
            $oldPath = str_replace('/storage/', '', $user->avatar_url); 
            Storage::disk('public')->delete($oldPath);
        }

        // 3. Guardar la nueva foto
        // El archivo se guardará en 'storage/app/public/avatars'
        $path = $request->file('avatar')->store('avatars', 'public');

        // 4. Actualizar la base de datos
        // Guardamos la URL pública (ej: '/storage/avatars/nombre.jpg')
        $user->avatar_url = Storage::url($path);
        $user->save();

        // 5. Devolver la respuesta con el usuario actualizado
        return response()->json([
            'message' => 'Foto de perfil actualizada exitosamente.',
            'user' => $user
        ]);
    }
}