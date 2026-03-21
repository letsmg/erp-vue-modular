<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\RecuperarSenhaMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Valida a localização do IP. 
     * Se o IP for do exterior, o login é interrompido imediatamente.
     */
    private function validateGeographicAccess(): void
    {
        $ip = request()->ip();

        // Ignora verificação em ambiente local (localhost)
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return;
        }

        try {
            // Consulta a API com timeout de 2 segundos para não atrasar a experiência do usuário
            $response = Http::timeout(2)->get("http://ip-api.com/json/{$ip}?fields=status,countryCode,message");

            if ($response->successful() && $response->json('status') === 'success') {
                $countryCode = $response->json('countryCode');

                // BLOQUEIO: Se o país não for Brasil (BR), impede o acesso
                if ($countryCode !== 'BR') {
                    // Logamos a tentativa suspeita para segurança do administrador
                    logger()->warning("Tentativa de login bloqueada por geolocalização", [
                        'ip' => $ip,
                        'pais_detectado' => $countryCode,
                        'email_tentativa' => request('email')
                    ]);

                    throw ValidationException::withMessages([
                        'email' => ['Acesso negado: Este sistema não aceita logins originados fora do Brasil por razões de segurança.'],
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Se a API de GeoIP falhar, registramos o erro no log interno,
            // mas deixamos o usuário tentar o login para não derrubar o sistema por erro de terceiros.
            logger()->error("Falha no serviço de verificação de IP: " . $e->getMessage());
        }
    }

    /**
     * Realiza o processo de login com dupla verificação (Geográfica + Credenciais)
     */
    public function login(array $credentials, $remember = false)
    {
        // 1. Verifica se o IP é do Brasil antes de qualquer coisa
        $this->validateGeographicAccess();

        // 2. Se passou na geolocalização, verifica as credenciais no banco
        return Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'is_active' => true
        ], $remember);
    }

    public function register(array $data)
    {
        $data['password'] = Hash::make($data['password']);
        return User::create($data);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    public function sendResetLink(string $email)
    {
        $url = route('login');
        Mail::to($email)->send(new RecuperarSenhaMail($url));
    }
}