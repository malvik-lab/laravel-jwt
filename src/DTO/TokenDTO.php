<?php

namespace MalvikLab\LaravelJwt\DTO;

final readonly class TokenDTO
{
    public function __construct(
        public string $iss,
        public string $sub,
        public string $jti,
        public null | int $iat,
        public null | int $exp,
    ) {
    }
}
