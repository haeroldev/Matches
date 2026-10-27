<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use JsonException;
use LogicException;

class JwtTokenService
{
    public function issue(User $user): string
    {
        $issuedAt = now()->timestamp;
        $header = $this->base64UrlEncode(json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT',
        ], JSON_THROW_ON_ERROR));
        $payload = $this->base64UrlEncode(json_encode([
            'iss' => config('app.url'),
            'sub' => (string) $user->getKey(),
            'iat' => $issuedAt,
            'exp' => $issuedAt + $this->ttlInSeconds(),
            'ver' => $user->jwt_version,
        ], JSON_THROW_ON_ERROR));
        $signature = $this->base64UrlEncode(hash_hmac(
            'sha256',
            "{$header}.{$payload}",
            $this->secret(),
            true,
        ));

        return "{$header}.{$payload}.{$signature}";
    }

    public function authenticate(Request $request): ?User
    {
        $authorization = $request->bearerToken();
        $token = $authorization ?: $request->cookie('jwt');

        if (! is_string($token) || $token === '') {
            return null;
        }

        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        [$header, $payload, $signature] = $parts;
        $decodedHeader = $this->decodeJson($header);
        $claims = $this->decodeJson($payload);
        $providedSignature = $this->base64UrlDecode($signature);

        if (
            $decodedHeader === null
            || ($decodedHeader['alg'] ?? null) !== 'HS256'
            || ($decodedHeader['typ'] ?? null) !== 'JWT'
            || $claims === null
            || $providedSignature === null
        ) {
            return null;
        }

        $expectedSignature = hash_hmac('sha256', "{$header}.{$payload}", $this->secret(), true);

        if (! hash_equals($expectedSignature, $providedSignature)) {
            return null;
        }

        $now = now()->timestamp;
        $userId = filter_var($claims['sub'] ?? null, FILTER_VALIDATE_INT);

        if (
            ($claims['iss'] ?? null) !== config('app.url')
            || ! is_int($claims['iat'] ?? null)
            || ! is_int($claims['exp'] ?? null)
            || $claims['iat'] > $now
            || $claims['exp'] <= $now
            || ! is_int($claims['ver'] ?? null)
            || $userId === false
            || $userId < 1
        ) {
            return null;
        }

        $user = User::query()->find($userId);

        return $user && $user->jwt_version === $claims['ver'] ? $user : null;
    }

    public function revoke(User $user): void
    {
        $user->increment('jwt_version');
    }

    public function ttlInMinutes(): int
    {
        return max(1, (int) config('jwt.ttl', 60));
    }

    private function ttlInSeconds(): int
    {
        return $this->ttlInMinutes() * 60;
    }

    private function secret(): string
    {
        $secret = (string) config('jwt.secret');

        if (str_starts_with($secret, 'base64:')) {
            $decoded = base64_decode(substr($secret, 7), true);
            $secret = $decoded === false ? '' : $decoded;
        }

        if ($secret === '') {
            throw new LogicException('Set APP_KEY or JWT_SECRET before issuing JWTs.');
        }

        return $secret;
    }

    private function decodeJson(string $encoded): ?array
    {
        $decoded = $this->base64UrlDecode($encoded);

        if ($decoded === null) {
            return null;
        }

        try {
            $value = json_decode($decoded, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        return is_array($value) ? $value : null;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): ?string
    {
        if (! preg_match('/^[A-Za-z0-9_-]+$/', $value)) {
            return null;
        }

        $decoded = base64_decode(strtr($value, '-_', '+/').str_repeat('=', (4 - strlen($value) % 4) % 4), true);

        return $decoded === false ? null : $decoded;
    }
}
