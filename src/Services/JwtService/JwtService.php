<?php

namespace MalvikLab\LaravelJwt\Services\JwtService;

use Firebase\JWT\JWT as FirebaseJwt;
use Firebase\JWT\Key as FirebaseKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Contracts\Auth\Authenticatable;
use MalvikLab\LaravelJwt\DTO\TokenPairDTO;
use MalvikLab\LaravelJwt\Models\AuthToken;

readonly class JwtService
{
    protected string $alg;
    protected string $publicKey;
    protected string $privateKey;
    protected int $accessTokenTtl;
    protected int $refreshTokenTtl;

    public function __construct(
        string $alg,
        string $publicKeyFilePath,
        string $privateKeyFilePath,
        int $accessTokenTtl,
        int $refreshTokenTtl
    )
    {
        $this->alg = $alg;
        $this->publicKey = File::get($publicKeyFilePath);
        $this->privateKey = File::get($privateKeyFilePath);
        $this->accessTokenTtl = $accessTokenTtl;
        $this->refreshTokenTtl = $refreshTokenTtl;
    }

    /**
     * @return int
     */
    public function ttl(): int
    {
        return $this->accessTokenTtl;
    }

    /**
     * @param Authenticatable $user
     * @return TokenPairDTO
     */
    public function makeTokens(Authenticatable $user): TokenPairDTO
    {
        $atJti = Str::uuid()->toString();
        $atExp = $this->accessTokenTtl > 0 ? Carbon::now()->addSeconds($this->accessTokenTtl)->unix() : null;
        $payload = [...$this->basePayload($user),
            'jti' => $atJti,
            'exp' => $atExp,
            'user' => $user->toArray()
        ];
        $accessToken = $this->encode($payload);

        $rtJti = Str::uuid()->toString();
        $rtExp = $this->refreshTokenTtl > 0 ? Carbon::now()->addSeconds($this->refreshTokenTtl)->unix() : null;
        $payload = [...$this->basePayload($user),
            'jti' => $rtJti,
            'exp' => $rtExp,
        ];
        $refreshToken = $this->encode($payload);

        $authToken = AuthToken::create([
            'user_id' => $user->getAuthIdentifier(),
            'at_jti' => $atJti,
            'at_exp' => $atExp,
            'rt_jti' => $rtJti,
            'rt_exp' => $rtExp,
        ]);

        return new TokenPairDTO(
            $accessToken,
            $refreshToken,
            $authToken
        );
    }

    /**
     * @param array $tokenData
     * @return Builder|AuthToken|null
     */
    public function userFromTokenData(array $tokenData): null | Builder | AuthToken
    {
        return AuthToken::query()
            ->where('at_jti', $tokenData['jti'])
            ->orWhere('rt_jti', $tokenData['jti'])
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

    /**
     * @param Authenticatable $user
     * @return array
     */
    private function basePayload(Authenticatable $user): array
    {
        return [
            'iss' => config('app.url'),
            'sub' => $user->getAuthIdentifier(),
            'iat' => Carbon::now()->unix(),
            'jti' => null,
            'exp' => null,
        ];
    }

    /**
     * @param array $payload
     * @return string
     */
    public function encode(array $payload): string
    {
        return FirebaseJwt::encode(
            $payload,
            $this->privateKey,
            $this->alg
        );
    }

    /**
     * @param string $accessToken
     * @return array
     */
    public function decode(string $accessToken): array
    {
        return (array)FirebaseJwt::decode(
            $accessToken,
            new FirebaseKey($this->publicKey, $this->alg)
        );
    }
}