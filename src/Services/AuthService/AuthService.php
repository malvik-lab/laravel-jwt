<?php

namespace MalvikLab\LaravelJwt\Services\AuthService;

use App\Rules\LoginRules;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use MalvikLab\LaravelJwt\Http\Requests\LoginRequest;

readonly class AuthService
{
    public function __construct()
    {
    }

    /**
     * @param array $credentials
     * @return User
     * @throws ValidationException|AuthenticationException
     */
    public function checkCredentials(array $credentials = []): User
    {
        $loginRequest = new LoginRequest();
        $validator = Validator::make($credentials, $loginRequest->rules());
        if ( $validator->fails() )
        {
            throw ValidationException::withMessages($validator->errors()->getMessages());
        }
        $filters = $validator->validated();

        $password = $filters['password'];
        unset($filters['password']);

        $query = User::query();
        foreach ( $filters as $field => $value )
        {
            $query->where($field, $value);
        }
        $user = $query->first();

        if (
            is_null($user) ||
            !Hash::check($password, $user->password)
        ) {
            throw new AuthenticationException('These credentials do not match our records.');
        }

        return $user;
    }
}
