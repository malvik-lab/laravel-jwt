<?php

namespace MalvikLab\LaravelJwt\Services\JwtService;

class TokenOptions
{
    private null | int $accessTokenTtl;
    private null | int $refreshTokenTtl;
    private array $roles;
    private array $permissions;
    private bool $stealth;

    public function __construct()
    {
        $this->accessTokenTtl = config('jwt.access_token_ttl');
        $this->refreshTokenTtl = config('jwt.refresh_token_ttl');
        $this->roles = [];
        $this->permissions = [];
        $this->stealth = false;
    }

    /**
     * @return int|null
     */
    public function getAccessTokenTtl(): null | int
    {
        return $this->accessTokenTtl;
    }

    /**
     * @return int|null
     */
    public function getRefreshTokenTtl(): null | int
    {
        return $this->refreshTokenTtl;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @return array
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * @return bool
     */
    public function getStealth(): bool
    {
        return $this->stealth;
    }

    /**
     * @param int|null $accessTokenTtl
     * @return void
     */
    public function setAccessTokenTtl(null | int $accessTokenTtl): void
    {
        $this->accessTokenTtl = $accessTokenTtl;
    }

    /**
     * @param int|null $refreshTokenTtl
     * @return void
     */
    public function setRefreshTokenTtl(null | int $refreshTokenTtl): void
    {
        $this->refreshTokenTtl = $refreshTokenTtl;
    }

    /**
     * @param string $role
     * @return void
     */
    public function setRole(string $role): void
    {
        $this->roles = [$role];
    }

    /**
     * @param array $roles
     * @return void
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * @param string $permission
     * @return void
     */
    public function setPermission(string $permission): void
    {
        $this->permissions = [$permission];
    }

    /**
     * @param bool $stealth
     * @return void
     */
    public function setStealth(bool $stealth): void
    {
        $this->stealth = $stealth;
    }

    /**
     * @param array $permissions
     * @return void
     */
    public function setPermissions(array $permissions): void
    {
        $this->permissions = $permissions;
    }
}