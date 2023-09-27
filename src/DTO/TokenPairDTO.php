<?php

namespace MalvikLab\LaravelJwt\DTO;

use MalvikLab\LaravelJwt\Models\AuthToken;

final readonly class TokenPairDTO
{
    public function __construct(
        public string $accessToken,
        public string $refreshToken,
        public AuthToken $authToken
    ) {
    }
}
