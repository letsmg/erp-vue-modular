<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Modules\User\Models\User;
use Exception;

class JwtService
{
    private string $secret;
    private int $expiry;

    public function __construct()
    {
        $this->secret = config('app.key') ?: 'your-secret-key';
        $this->expiry = config('auth.jwt.expiry', 86400); // 24 horas por padrão
    }

    public function generateToken(User $user): string
    {
        $payload = [
            'iss' => config('app.url'),
            'iat' => time(),
            'exp' => time() + $this->expiry,
            'sub' => $user->id,
            'email' => $user->email,
        ];

        return JWT::encode($payload, $this->secret, 'HS256');
    }

    public function validateToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, 'HS256'));
            return (array) $decoded;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getUserFromToken(string $token): ?User
    {
        $decoded = $this->validateToken($token);
        
        if (!$decoded || !isset($decoded['sub'])) {
            return null;
        }

        return User::find($decoded['sub']);
    }
}
