<?php

return [
    'alg' => env('JWT_ALG'),
    'public_key_file_path' => env('JWT_PUBLIC_KEY_FILE_PATH'),
    'private_key_file_path' => env('JWT_PRIVATE_KEY_FILE_PATH'),
    'access_token_ttl' => env('JWT_ACCESS_TOKEN_TTL', 0),
    'refresh_token_ttl' => env('JWT_REFRESH_TOKEN_TTL', 0),
];