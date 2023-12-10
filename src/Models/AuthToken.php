<?php

namespace MalvikLab\LaravelJwt\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static create(array $array)
 * @property string $user_id
 * @property string $at_jti
 * @property int $at_exp
 * @property string $rt_jti
 * @property int $rt_exp
 * @property string $ip
 * @property string ip_details
 * @property string $user_agent
 * @property mixed $roles
 * @property mixed $permissions
 */

class AuthToken extends Model
{
    use SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'roles',
        'permissions',
        'at_jti',
        'at_exp',
        'rt_jti',
        'rt_exp',
        'ip',
        'ip_details',
        'user_agent'
    ];

    protected $casts = [
        'roles' => 'array',
        'permissions' => 'array',
        'ip_details' => 'array',
    ];
}
