<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;


class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }
    

    public function login(Request $request) 
    {
        $request->validate([
            'usu_codigo' => 'required',
            'usu_clave' => 'required',
        ]);
    
        $user = User::where('usu_codigo', $request->usu_codigo)->first();
    
        if (!$user) {
            return back()->withErrors(['usu_codigo' => 'Usuario no encontrado']);
        }
    
        $inputPassword = md5($request->usu_clave); // Convertir a MD5
        $storedPassword = $user->usu_clave;
    
        if ($inputPassword === $storedPassword) {
            Auth::login($user, true);
            if ($user->usu_nivel >= 900) {
                return redirect()->intended('/dashboard');
            } elseif($user->rol == 'muestreador') {
                return redirect()->intended('/mis-tareas');
            } elseif($user->rol == 'laboratorio') {
                return redirect()->intended('/mis-ordenes');
            } elseif($user->rol == 'coordinador_lab') {
                return redirect()->intended('/dashboard/analisis');
            } elseif($user->rol == 'coordinador_muestreo') {
                return redirect()->intended('/dashboard/muestreo');
            } else {
                return redirect()->intended('/login');
            }
        } 

        return back()->withErrors(['usu_clave' => 'Contraseña incorrecta']);
    }

    
    public function logout(Request $request)
    {
        Auth::logout();
        return redirect('/login');
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return view('auth.show', compact('user'));
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('auth.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $isAdmin = $user->usu_nivel >= 900;
    
        // Filtrar solo los campos que se deben actualizar
        $data = $request->only(['usu_descripcion', 'usu_clave', 'usu_codigo']);
    
        if ($isAdmin) {
            $data = array_merge($data, $request->only([
                'usu_correo', 'usu_nivel', 'usu_estado', 'rol'
            ]));
        }
    
        // Verificar si se envió una nueva clave, si es así, encriptarla con MD5
        if (!empty($data['usu_clave'])) {
            $data['usu_clave'] = md5($data['usu_clave']);  // Encriptado con MD5
        } else {
            unset($data['usu_clave']); // Si no se proporciona clave, no se actualiza
        }
    
        // Llenar y guardar el usuario con los nuevos datos
        $user->fill($data);
        $user->save();
    
        return redirect()->route('auth.show', $user->usu_codigo)->with('success', 'Perfil actualizado correctamente.');
    }
    
    public function showSecurity($id)
    {
        $user = User::findOrFail($id);
        return view('auth.security', compact('user'));
    }
    
    public function showHelp($id)
    {
        $user = User::findOrFail($id);
        return view('auth.help', compact('user'));
    }
    
}
