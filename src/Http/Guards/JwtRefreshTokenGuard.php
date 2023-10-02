<?php

namespace MalvikLab\LaravelJwt\Http\Guards;

use Illuminate\Contracts\Auth\UserProvider;
use MalvikLab\LaravelJwt\Enum\TokenTypeEnum;

class JwtRefreshTokenGuard extends AbstractJwtGuard
{
    public function __construct(UserProvider $userProvider)
    {
        parent::__construct($userProvider);
    }

    /**
     * @return bool
     */
    public function check(): bool
    {
        return $this->mainCheck(TokenTypeEnum::REFRESH_TOKEN);
    }

    /**
     * @return void
     */
    public function refresh()
    {
        $user = $this->user();
        $options = $this->jwtService->tokenOptionsByAuthToken($this->authToken());

        $this->logout();
        $this->login($user, $options);
    }
}
