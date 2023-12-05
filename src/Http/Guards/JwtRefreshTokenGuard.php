<?php

namespace MalvikLab\LaravelJwt\Http\Guards;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Request;
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
     * @param string|null $ip
     * @param string|null $userAgent
     * @return void
     */
    public function refresh(
        null | string $ip = null,
        null | string $userAgent = null
    ): void
    {
        $user = $this->user();
        $options = $this->jwtService->tokenOptionsByAuthToken($this->authToken());

        $this->logout();
        $this->login(
            $user,
            $options,
            $ip,
            $userAgent
        );
    }
}
