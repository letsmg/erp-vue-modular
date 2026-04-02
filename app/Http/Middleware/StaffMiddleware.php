<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\AccessLevel;

class StaffMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verifica se o usuário está autenticado e se é da equipe (Admin ou Operador)
        if (auth()->check() && auth()->user()->isStaff()) {
            return $next($request);
        }

        // Se for um cliente logado tentando acessar o administrativo, redirecionamos para a store
        if (auth()->check() && auth()->user()->isClient()) {
            return redirect()->route('store.index')->with('info', 'Você foi redirecionado para a loja.');
        }

        // Para outros casos (não logado), o middleware 'auth' já redirecionará
        return redirect()->route('login')->with('error', 'Acesso negado.');
    }
}
