<?php

namespace MalvikLab\LaravelJwt\Providers;

use Illuminate\Support\ServiceProvider;
use MalvikLab\LaravelJwt\Services\JwtService\JwtService;
use MalvikLab\LaravelJwt\Console\Commands\Jwt as JwtCommand;

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
                config('jwt.access_token_private_key_file_path'),
                config('jwt.access_token_public_key_file_path'),
                config('jwt.refresh_token_private_key_file_path'),
                config('jwt.refresh_token_public_key_file_path')
            );
        });

        $this->commands([
            JwtCommand::class,
        ]);
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

        $createAuthTokensTableMigration = '2023_09_25_125422_create_auth_tokens_table.php';
        $addIpAndUserAgentToAuthTokensTableMigration = '2023_12_05_092449_add_ip_and_user_agent_to_auth_tokens_table.php';
        $addIpDetailsToAuthTokensTableMigration = '2023_12_05_092450_add_ip_details_to_auth_tokens_table.php';
        $this->publishes([
            sprintf('%s/../../database/migrations/%s', __DIR__, $createAuthTokensTableMigration) => database_path(sprintf('migrations/%s', $createAuthTokensTableMigration)),
            sprintf('%s/../../database/migrations/%s', __DIR__, $addIpAndUserAgentToAuthTokensTableMigration) => database_path(sprintf('migrations/%s', $addIpAndUserAgentToAuthTokensTableMigration)),
            sprintf('%s/../../database/migrations/%s', __DIR__, $addIpDetailsToAuthTokensTableMigration) => database_path(sprintf('migrations/%s', $addIpDetailsToAuthTokensTableMigration)),
        ], 'jwt-migration');
    }
}
