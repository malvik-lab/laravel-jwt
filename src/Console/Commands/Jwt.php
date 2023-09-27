<?php

namespace MalvikLab\LaravelJwt\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class Jwt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jwt:keys';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate JWT keys';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $keysDirectoryPath = config('jwt.keys_directory_path');

        if ( is_null($keysDirectoryPath) )
        {
            $keysDirectoryPath = base_path('keys');
        }

        $accessTokenPrivateKeyPath = sprintf('%s/access_token_private_key.pem', $keysDirectoryPath);
        $accessTokenPublicKeyPath = sprintf('%s/access_token_public_key.pem', $keysDirectoryPath);
        $refreshTokenPrivateKeyPath = sprintf('%s/refresh_token_private_key.pem', $keysDirectoryPath);
        $refreshTokenPublicKeyPath = sprintf('%s/refresh_token_public_key.pem', $keysDirectoryPath);

        if ( File::exists($keysDirectoryPath) )
        {
            $this->error(sprintf('Directory "%s" already exists.', $keysDirectoryPath));

            return SymfonyCommand::FAILURE;
        }

        if ( !$this->checkEnv() )
        {
            return SymfonyCommand::FAILURE;
        }

        File::makeDirectory($keysDirectoryPath);

        exec(sprintf('openssl genrsa -out %s 2048', $accessTokenPrivateKeyPath));
        exec(sprintf('openssl rsa -in %s -pubout -out %s 2>/dev/null', $accessTokenPrivateKeyPath, $accessTokenPublicKeyPath));

        exec(sprintf('openssl genrsa -out %s 2048', $refreshTokenPrivateKeyPath));
        exec(sprintf('openssl rsa -in %s -pubout -out %s 2>/dev/null', $refreshTokenPrivateKeyPath, $refreshTokenPublicKeyPath));

        File::append('.env', PHP_EOL . 'JWT_ALG=RS256' . PHP_EOL);

        File::append('.env', 'JWT_ACCESS_TOKEN_PRIVATE_KEY_FILE_PATH=' . $accessTokenPrivateKeyPath . PHP_EOL);
        File::append('.env', 'JWT_ACCESS_TOKEN_PUBLIC_KEY_FILE_PATH=' . $accessTokenPublicKeyPath . PHP_EOL);
        File::append('.env', 'JWT_ACCESS_TOKEN_TTL=14400' . PHP_EOL);

        File::append('.env', 'JWT_REFRESH_TOKEN_PRIVATE_KEY_FILE_PATH=' . $refreshTokenPrivateKeyPath . PHP_EOL);
        File::append('.env', 'JWT_REFRESH_TOKEN_PUBLIC_KEY_FILE_PATH=' . $refreshTokenPublicKeyPath . PHP_EOL);
        File::append('.env', 'JWT_REFRESH_TOKEN_TTL=2592000' . PHP_EOL);

        $this->info('The keys have been generated and the configuration saved successfully.');

        return SymfonyCommand::SUCCESS;
    }

    /**
     * @return bool
     */
    private function checkEnv(): bool
    {
        $items = [
            'JWT_ALG',
            'JWT_KEYS_DIRECTORY_PATH',
            'JWT_ACCESS_TOKEN_PUBLIC_KEY_FILE_PATH',
            'JWT_ACCESS_TOKEN_PRIVATE_KEY_FILE_PATH',
            'JWT_ACCESS_TOKEN_TTL',
            'JWT_REFRESH_TOKEN_PUBLIC_KEY_FILE_PATH',
            'JWT_REFRESH_TOKEN_PRIVATE_KEY_FILE_PATH',
            'JWT_REFRESH_TOKEN_TTL',
        ];

        foreach ( $items as $item )
        {
            if ( is_string(getenv($item)) )
            {
                $this->error(sprintf('Env file already contains "%s".', $item));

                return false;
            }
        }

        return true;
    }
}
