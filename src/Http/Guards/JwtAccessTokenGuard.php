<?php

namespace MalvikLab\LaravelJwt\Http\Guards;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\AuthenticationException;
use MalvikLab\LaravelJwt\Enum\TokenTypeEnum;
use MalvikLab\LaravelJwt\Services\JwtService\TokenOptions;

class JwtAccessTokenGuard extends AbstractJwtGuard
{
    public function __construct(UserProvider $userProvider)
    {
        parent::__construct($userProvider);
    }

    /**
     * @param array $credentials
     * @param TokenOptions $options
     * @return bool
     * @throws AuthenticationException
     */
    public function attempt(
        array $credentials,
        TokenOptions $options = new TokenOptions()
    ): bool
    {
        if ( !array_key_exists('password', $credentials) )
        {
            throw new AuthenticationException('Invalid credentials');
        }

        $user = $this->userProvider->retrieveByCredentials($credentials);

        if (
            is_null($user) ||
            !Hash::check($credentials['password'], $user->getAuthPassword())
        ) {
            throw new AuthenticationException('Invalid credentials');
        }

        $this->login($user, $options);

        return true;
    }

    /**
     * @return bool
     */
    public function check(): bool
    {
        return $this->mainCheck(TokenTypeEnum::ACCESS_TOKEN);
    }
}
