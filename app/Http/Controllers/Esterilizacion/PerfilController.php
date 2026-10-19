<?php

namespace App\Http\Controllers\Esterilizacion;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
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
}
