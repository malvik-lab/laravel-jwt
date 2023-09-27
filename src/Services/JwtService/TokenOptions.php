<?php

namespace MalvikLab\LaravelJwt\Services\JwtService;

class TokenOptions
{
    private int $accessTokenTtl;
    private int $refreshTokenTtl;
    private array $roles;
    private array $permissions;

    public function __construct()
    {
        $this->accessTokenTtl = config('jwt.access_token_ttl');
        $this->refreshTokenTtl = config('jwt.refresh_token_ttl');
        $this->roles = [];
        $this->permissions = [];
    }

    /**
     * @return int
     */
    public function getAccessTokenTtl(): int
    {
        return $this->accessTokenTtl;
    }

    /**
     * @return int
     */
    public function getRefreshTokenTtl(): int
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
     * @param int $accessTokenTtl
     * @return void
     */
    public function setAccessTokenTtl(int $accessTokenTtl): void
    {
        $this->accessTokenTtl = $accessTokenTtl;
    }

    /**
     * @param int $refreshTokenTtl
     * @return void
     */
    public function setRefreshTokenTtl(int $refreshTokenTtl): void
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
     * @param array $permissions
     * @return void
     */
    public function setPermissions(array $permissions): void
    {
        $this->permissions = $permissions;
    }
}