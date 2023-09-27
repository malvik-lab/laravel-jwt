<?php

return [
    'alg' => env('JWT_ALG'),
    'keys_directory_path' => env('JWT_KEYS_DIRECTORY_PATH'),
    'access_token_private_key_file_path' => env('JWT_ACCESS_TOKEN_PRIVATE_KEY_FILE_PATH'),
    'access_token_public_key_file_path' => env('JWT_ACCESS_TOKEN_PUBLIC_KEY_FILE_PATH'),
    'access_token_ttl' => env('JWT_ACCESS_TOKEN_TTL', 0),
    'refresh_token_private_key_file_path' => env('JWT_REFRESH_TOKEN_PRIVATE_KEY_FILE_PATH'),
    'refresh_token_public_key_file_path' => env('JWT_REFRESH_TOKEN_PUBLIC_KEY_FILE_PATH'),
    'refresh_token_ttl' => env('JWT_REFRESH_TOKEN_TTL', 0),
];