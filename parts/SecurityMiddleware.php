<?php
declare(strict_types=1);

class SecurityMiddleware
{
    public function applyHeaders(): void
    {
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 1; mode=block');
    }

    public function generateCsrfToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function validateCsrfToken(string $token): bool
    {
        return hash_equals($token, $_SESSION['csrf_token'] ?? '');
    }
}