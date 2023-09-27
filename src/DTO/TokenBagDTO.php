<?php

namespace MalvikLab\LaravelJwt\DTO;

use MalvikLab\LaravelJwt\Models\AuthToken;
use MalvikLab\LaravelJwt\Services\JwtService\TokenOptions;

final readonly class TokenBagDTO
{
    public function __construct(
        public string $accessToken,
        public string $refreshToken,
        public AuthToken $authToken,
        public TokenOptions $tokenOptions
    ) {
    }
}
