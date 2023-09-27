<?php

namespace MalvikLab\LaravelJwt\Http\Guards;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\AuthenticationException;
use MalvikLab\LaravelJwt\Models\AuthToken;
use MalvikLab\LaravelJwt\Services\JwtService\JwtService;
use Exception;

class JwtGuard implements Guard
{
    private Request $request;
    private null | Authenticatable $user;
    private JwtService $jwtService;
    private null | string $accessTokenString;
    private null | string $refreshTokenString;
    private null | AuthToken $authToken;

    public function __construct(private readonly UserProvider $userProvider)
    {
        $this->request = request();
        $this->user = null;
        $this->jwtService = app(JwtService::class);
        $this->accessTokenString = null;
        $this->refreshTokenString = null;
        $this->authToken = null;
    }

    /**
     * @throws AuthenticationException
     */
    public function attempt(array $credentials): bool
    {
        if ( !array_key_exists('password', $credentials) )
        {
            return false;
        }

        $user = $this->userProvider->retrieveByCredentials($credentials);

        if (
            is_null($user) ||
            !Hash::check($credentials['password'], $user->getAuthPassword())
        ) {
            throw new AuthenticationException('Invalid credentials');
        }

        $this->login($user);

        return true;
    }

    /**
     * @param Authenticatable $user
     * @return void
     */
    public function login(Authenticatable $user): void
    {
        $tokenPairDTO = $this->jwtService->makeTokens($user);

        $this->setUser($user);
        $this->setAccessTokenString($tokenPairDTO->accessToken);
        $this->setRefreshTokenString($tokenPairDTO->refreshToken);
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
     * @return void
     */
    public function refresh(): void
    {
        $this->logout();
        $this->login($this->user());
    }

    /**
     * @return bool
     */
    public function check(): bool
    {
        $tokenData = $this->decodeAccessToken();
        if ( is_null($tokenData) )
        {
            return false;
        }

        $authToken = $this->jwtService->userFromTokenData($tokenData);

        if (
            is_null($authToken) ||
            $authToken->user_id != $tokenData['sub']
        ) {
            return false;
        }

        $user = $this->userProvider->retrieveById($tokenData['sub']);

        if ( is_null($user) )
        {
            return false;
        }

        $this->setAuthToken($authToken);
        $this->setUser($user);

        return true;
    }

    public function guest(): bool
    {
        return is_null($this->user);
    }

    public function user(): null | Authenticatable
    {
        return $this->user;
    }

    public function authToken(): null | AuthToken
    {
        return $this->authToken;
    }

    public function id(): null | int | string
    {
        if ( $this->guest() )
        {
            return null;
        }

        return $this->user->getAuthIdentifier();
    }

    public function validate(array $credentials = [])
    {
        // TODO: Implement validate() method.
    }

    public function hasUser(): bool
    {
        return !is_null($this->user);
    }

    public function setUser(Authenticatable $user): void
    {
        $this->user = $user;
    }

    public function setAuthToken(AuthToken $authToken): void
    {
        $this->authToken = $authToken;
    }

    public function setAccessTokenString(string $accessTokenString): void
    {
        $this->accessTokenString = $accessTokenString;
    }

    public function setRefreshTokenString(string $refreshTokenString): void
    {
        $this->refreshTokenString = $refreshTokenString;
    }

    public function accessToken(): null | string
    {
        return $this->accessTokenString;
    }

    public function refreshToken(): null | string
    {
        return $this->refreshTokenString;
    }

    public function response(): JsonResponse
    {
        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $this->accessToken(),
            'refresh_token' => $this->refreshToken(),
            'expire_in' => $this->jwtService->ttl(),
        ]);
    }

    private function decodeAccessToken(): null | array
    {
        $accessToken = $this->request->bearerToken();

        try {
            return $this->jwtService->decode($accessToken);
        } catch (Exception $e) {
            return null;
        }
    }

    private function reset(): void
    {
        $this->user = null;
        $this->accessTokenString = null;
        $this->refreshTokenString = null;
        $this->authToken = null;
    }
}