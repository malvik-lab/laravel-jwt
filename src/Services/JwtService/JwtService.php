<?php

namespace MalvikLab\LaravelJwt\Services\JwtService;

use Firebase\JWT\JWT as FirebaseJwt;
use Firebase\JWT\Key as FirebaseKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Contracts\Auth\Authenticatable;
use MalvikLab\LaravelJwt\DTO\TokenDTO;
use MalvikLab\LaravelJwt\DTO\TokenBagDTO;
use MalvikLab\LaravelJwt\Enum\TokenTypeEnum;
use MalvikLab\LaravelJwt\Models\AuthToken;
use Exception;

readonly class JwtService
{
    protected string $alg;
    protected string $accessTokenPrivateKey;
    protected string $accessTokenPublicKey;
    protected string $refreshTokenPrivateKey;
    protected string $refreshTokenPublicKey;

    public function __construct(
        string $alg,
        string $accessTokenPrivateKeyFilePath,
        string $accessTokenPublicKeyFilePath,
        string $refreshTokenPrivateKeyFilePath,
        string $refreshTokenPublicKeyFilePath,
    )
    {
        $this->alg = $alg;
        $this->accessTokenPrivateKey = File::get($accessTokenPrivateKeyFilePath);
        $this->accessTokenPublicKey = File::get($accessTokenPublicKeyFilePath);
        $this->refreshTokenPrivateKey = File::get($refreshTokenPrivateKeyFilePath);
        $this->refreshTokenPublicKey = File::get($refreshTokenPublicKeyFilePath);
    }

    /**
     * @param Authenticatable $user
     * @param TokenOptions $options
     * @return TokenBagDTO
     */
    public function makeTokens(Authenticatable $user, TokenOptions $options = new TokenOptions()): TokenBagDTO
    {
        $atJti = Str::uuid()->toString();
        $atExp = is_int($options->getAccessTokenTtl()) ? Carbon::now()->addSeconds($options->getAccessTokenTtl())->unix() : null;
        $payload = [...$this->basePayload($user),
            'token_type' => TokenTypeEnum::ACCESS_TOKEN->value,
            'jti' => $atJti,
            'exp' => $atExp,
            'user' => $user->toArray(),
            'roles' => $options->getRoles(),
            'permissions' => $options->getPermissions(),
        ];
        $accessToken = $this->encode(TokenTypeEnum::ACCESS_TOKEN, $payload);

        $rtJti = Str::uuid()->toString();
        $rtExp = is_int($options->getRefreshTokenTtl()) ? Carbon::now()->addSeconds($options->getRefreshTokenTtl())->unix() : null;
        $payload = [...$this->basePayload($user),
            'token_type' => TokenTypeEnum::REFRESH_TOKEN->value,
            'jti' => $rtJti,
            'exp' => $rtExp,
        ];
        $refreshToken = $this->encode(TokenTypeEnum::REFRESH_TOKEN, $payload);

        if ( $options->getStealth() )
        {
            $authToken = null;
        } else {
            $authToken = AuthToken::create([
                'user_id' => $user->getAuthIdentifier(),
                'roles' => $options->getRoles(),
                'permissions' => $options->getPermissions(),
                'at_jti' => $atJti,
                'at_exp' => $atExp,
                'rt_jti' => $rtJti,
                'rt_exp' => $rtExp,
            ]);
        }

        return new TokenBagDTO(
            $accessToken,
            $refreshToken,
            $authToken,
            $options
        );
    }

    public function tokenOptionsByAuthToken(AuthToken $authToken): TokenOptions
    {
        $tokenOptions = new TokenOptions();
        $tokenOptions->setRoles($authToken->roles);
        $tokenOptions->setPermissions($authToken->permissions);

        return $tokenOptions;
    }

    public function authTokenByAccessTokenJti(string $jti): null | Builder | AuthToken
    {
        return AuthToken::query()
            ->where('at_jti', $jti)
            ->where('at_revoked', 0)
            ->first();
    }

    /**
     * @param TokenDTO $tokenDTO
     * @param int|string $userId
     * @return Builder|AuthToken|null
     */
    public function authTokenByAccessToken(TokenDTO $tokenDTO, int | string $userId): null | Builder | AuthToken
    {
        return AuthToken::query()
            ->where('user_id', $userId)
            ->where('at_jti', $tokenDTO->jti)
            ->where('at_revoked', 0)
            ->first();
    }

    /**
     * @param TokenDTO $tokenDTO
     * @param int|string $userId
     * @return Builder|AuthToken|null
     */
    public function authTokenByRefreshToken(TokenDTO $tokenDTO, int | string $userId): null | Builder | AuthToken
    {
        return AuthToken::query()
            ->where('user_id', $userId)
            ->where('rt_jti', $tokenDTO->jti)
            ->where('rt_revoked', 0)
            ->first();
    }

    /**
     * @param AuthToken $authToken
     * @return void
     */
    public function deleteAuthToken(AuthToken $authToken): void
    {
        $authToken->delete();
    }

    public function deleteUserAuthTokens(User $user, null | AuthToken $excludeAuthToken = null): void
    {
        $query = AuthToken::query();
        $query->where('user_id', $user->id);

        if ( $excludeAuthToken )
        {
            $query->whereNot('id', $excludeAuthToken->id);
        }

        $query->delete();
    }

    /**
     * @param Authenticatable $user
     * @return array
     */
    private function basePayload(Authenticatable $user): array
    {
        return [
            'iss' => config('app.url'),
            'sub' => $user->getAuthIdentifier(),
            'token_type' => null,
            'iat' => Carbon::now()->unix(),
            'jti' => null,
            'exp' => null,
        ];
    }

    /**
     * @param TokenTypeEnum $tokenTypeEnum
     * @param array $payload
     * @return string
     */
    public function encode(TokenTypeEnum $tokenTypeEnum, array $payload): string
    {
        $privateKey = match ($tokenTypeEnum)
        {
            TokenTypeEnum::ACCESS_TOKEN => $this->accessTokenPrivateKey,
            TokenTypeEnum::REFRESH_TOKEN => $this->refreshTokenPrivateKey,
        };

        return FirebaseJwt::encode(
            $payload,
            $privateKey,
            $this->alg
        );
    }

    /**
     * @param TokenTypeEnum $tokenTypeEnum
     * @param string $accessToken
     * @return array|null
     */
    public function decode(TokenTypeEnum $tokenTypeEnum, string $accessToken): null | array
    {
        $publicKey = match ($tokenTypeEnum)
        {
            TokenTypeEnum::ACCESS_TOKEN => $this->accessTokenPublicKey,
            TokenTypeEnum::REFRESH_TOKEN => $this->refreshTokenPublicKey,
        };

        try {
            return (array)FirebaseJwt::decode(
                $accessToken,
                new FirebaseKey($publicKey, $this->alg)
            );
        } catch (Exception $e) {
            return null;
        }
    }
}
