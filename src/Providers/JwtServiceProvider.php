<?php

namespace MalvikLab\LaravelJwt\Providers;

use Illuminate\Support\ServiceProvider;
use MalvikLab\LaravelJwt\Services\JwtService\JwtService;

class JwtServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(JwtService::class, function ($app) {
            return new JwtService(
                config('jwt.alg'),
                config('jwt.public_key_file_path'),
                config('jwt.private_key_file_path'),
                config('jwt.access_token_ttl'),
                config('jwt.refresh_token_ttl')
            );
        });
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        $fileName = 'jwt.php';
        $this->publishes([
            sprintf('%s/../../config/%s', __DIR__, $fileName) => config_path($fileName)
        ], 'jwt-config');

        $fileName = '2023_09_25_125422_create_auth_tokens_table.php';
        $this->publishes([
            sprintf('%s/../../database/migrations/%s', __DIR__, $fileName) => database_path(sprintf('migrations/%s', $fileName)),
        ], 'jwt-migration');
    }
}