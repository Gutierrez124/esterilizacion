<?php

namespace App\Http\Controllers\Esterilizacion;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PerfilController extends Controller
{
    public function show(): View
    {
        $usuario = Auth::user();

        $ultimoLogin = Auditoria::query()
            ->where('usuario_id', $usuario->getKey())
            ->where('accion', 'LOGIN_EXITOSO')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();

        return view('esterilizacion.perfil.show', [
            'usuario' => $usuario,
            'ultimoLogin' => $ultimoLogin,
            'totalAuditorias' => Auditoria::query()
                ->where('usuario_id', $usuario->getKey())
                ->count(),
            'registradoEl' => $usuario->creado_en
                ? Carbon::parse($usuario->creado_en)->format('d/m/Y H:i')
                : null,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $usuario = Auth::user();

        $datos = $request->validate([
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'dni' => ['nullable', 'string', 'max:20'],
            'codigo' => ['nullable', 'string', 'max:20'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('usuarios', 'email')->ignore($usuario->getKey()),
            ],
            'foto' => ['nullable', 'image', 'max:2048'],
        ], [
            'nombres.required' => 'Ingresa tus nombres.',
            'apellidos.required' => 'Ingresa tus apellidos.',
            'email.required' => 'Ingresa tu correo electronico.',
            'email.email' => 'El correo electronico no es valido.',
            'email.unique' => 'Ese correo ya esta en uso por otra cuenta.',
            'foto.image' => 'La foto debe ser una imagen (JPG, PNG o similar).',
            'foto.max' => 'La foto no puede pesar mas de 2 MB.',
        ]);

        if ($request->hasFile('foto')) {
            if ($usuario->foto) {
                Storage::delete($usuario->foto);
            }
            $datos['foto'] = $request->file('foto')->store('fotos-perfil', 'public');
        } else {
            unset($datos['foto']);
        }

        $usuario->fill($datos);
        $usuario->actualizado_en = now();
        $usuario->save();

        return redirect()
            ->route('esterilizacion.perfil.show')
            ->with('success', 'Perfil actualizado correctamente.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $usuario = Auth::user();

        $datos = $request->validate([
            'password_actual' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'password_actual.required' => 'Ingresa tu contrasena actual.',
            'password.required' => 'Ingresa la nueva contrasena.',
            'password.min' => 'La nueva contrasena debe tener al menos 6 caracteres.',
            'password.confirmed' => 'La confirmacion no coincide con la nueva contrasena.',
        ]);

        if (! Hash::check($datos['password_actual'], $usuario->password)) {
            throw ValidationException::withMessages([
                'password_actual' => 'La contrasena actual no es correcta.',
            ]);
        }

        $usuario->password = $datos['password'];
        $usuario->actualizado_en = now();
        $usuario->save();

        return redirect()
            ->route('esterilizacion.perfil.show')
            ->with('success', 'Contrasena actualizada correctamente.');
    }
}
