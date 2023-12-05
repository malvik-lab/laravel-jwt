<?php

namespace MalvikLab\LaravelJwt\Http\Guards;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use MalvikLab\LaravelJwt\DTO\TokenBagDTO;
use MalvikLab\LaravelJwt\DTO\TokenDTO;
use MalvikLab\LaravelJwt\Enum\TokenTypeEnum;
use MalvikLab\LaravelJwt\Models\AuthToken;
use MalvikLab\LaravelJwt\Services\JwtService\JwtService;
use MalvikLab\LaravelJwt\Services\JwtService\TokenOptions;

abstract class AbstractJwtGuard implements Guard
{
    private Request $request;
    public JwtService $jwtService;
    private null | Authenticatable $user;
    private null | AuthToken $authToken;
    private null | TokenBagDTO $tokenBagDTO;

    public function __construct(public UserProvider $userProvider)
    {
        $this->request = request();
        $this->jwtService = app(JwtService::class);
        $this->reset();
    }

    /**
     * @param Authenticatable $user
     * @param TokenOptions $options
     * @param string|null $ip
     * @param string|null $userAgent
     * @return void
     */
    public function login(
        Authenticatable $user,
        TokenOptions $options = new TokenOptions(),
        null | string $ip = null,
        null | string $userAgent = null
    ): void
    {
        $this->tokenBagDTO = $this->jwtService->makeTokens($user, $options, $ip, $userAgent);
    }

    /**
     * @return void
     */
    public function logout(): void
    {
        if ( !is_null($this->authToken()) )
        {
            $this->jwtService->deleteAuthToken($this->authToken());
            $this->reset();
        }
    }

    /**
     * @param TokenTypeEnum $tokenTypeEnum
     * @return bool
     */
    public function mainCheck(TokenTypeEnum $tokenTypeEnum): bool
    {
        $tokenDTO = $this->decodeAccessToken($tokenTypeEnum);

        if ( is_null($tokenDTO) )
        {
            return false;
        }

        $authToken = match ($tokenTypeEnum)
        {
            TokenTypeEnum::ACCESS_TOKEN => $this->jwtService->authTokenByAccessToken($tokenDTO, $tokenDTO->sub),
            TokenTypeEnum::REFRESH_TOKEN => $this->jwtService->authTokenByRefreshToken($tokenDTO, $tokenDTO->sub),
        };

        if ( is_null($authToken) )
        {
            return false;
        }

        $user = $this->userProvider->retrieveById($authToken->user_id);

        if ( is_null($user) )
        {
            return false;
        }

        $this->setUser($user);
        $this->setAuthToken($authToken);

        return true;
    }

    /**
     * @return string
     */
    public function bearerToken(): string
    {
        return (string)$this->request->bearerToken();
    }

    /**
     * @param TokenTypeEnum $tokenTypeEnum
     * @return TokenDTO|null
     */
    public function decodeAccessToken(TokenTypeEnum $tokenTypeEnum): null | TokenDTO
    {
        $token = $this->bearerToken();
        $tokenData = $this->jwtService->decode($tokenTypeEnum, $token);

        if ( is_null($tokenData) )
        {
            return null;
        }

        return new TokenDTO(
            $tokenData['iss'],
            $tokenData['sub'],
            $tokenData['jti'],
            $tokenData['iat'],
            array_key_exists('exp', $tokenData) ? $tokenData['exp'] : null,
        );
    }

    /**
     * @return JsonResponse
     */
    public function response(): JsonResponse
    {
        return response()
            ->json($this->getResponse());
    }

    public function getResponse(): array
    {
        return [
            'token_type' => 'Bearer',
            'access_token' => $this->tokenBagDTO->accessToken,
            'refresh_token' => $this->tokenBagDTO->refreshToken,
            'expire_in' => $this->tokenBagDTO->tokenOptions->getAccessTokenTtl(),
        ];
    }

    /**
     * @return void
     */
    public function reset(): void
    {
        $this->user = null;
        $this->authToken = null;
        $this->tokenBagDTO = null;
    }

    /**
     * @return Authenticatable|null
     */
    public function user(): null | Authenticatable
    {
        return $this->user;
    }

    /**
     * @return bool
     */
    public function hasUser(): bool
    {
        return !is_null($this->user());
    }

    /**
     * @return bool
     */
    public function guest(): bool
    {
        return is_null($this->user());
    }

    /**
     * @return int|mixed|string|null
     */
    public function id()
    {
        if ( $this->hasUser() )
        {
            return $this->user()->getAuthIdentifier();
        }

        return null;
    }

    /**
     * @param array $credentials
     * @return bool
     */
    public function validate(array $credentials = []): bool
    {
        return $this->attempt($credentials);
    }

    /**
     * @return null|AuthToken
     */
    public function authToken(): null | AuthToken
    {
        return $this->authToken;
    }

    /**
     * @param Authenticatable $user
     * @return void
     */
    public function setUser(Authenticatable $user): void
    {
        $this->user = $user;
    }

    /**
     * @param AuthToken $authToken
     * @return void
     */
    public function setAuthToken(AuthToken $authToken): void
    {
        $this->authToken = $authToken;
    }
}
