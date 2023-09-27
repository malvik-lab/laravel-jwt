# Laravel JWT

Laravel JWT is an open-source library for Laravel that adds JWT authentication to your project.  
JWT is a token-based authentication method that is secure, lightweight, and easy to implement.
The library is easy to use and can be configured in minutes. It provides a range of features for JWT authentication, including:
- Generating and validating JWT tokens
- Managing users and tokens
- Protecting routes and controllers

### Pre-installation (not mandatory)
JWT uses a pair of public and private keys to sign and verify tokens. The public key is used to verify tokens on the client side, while the private key is used to sign tokens on the server side.

The library by default stores the public and private keys in a folder called **keys** in the root of your project. You can change the location of this folder by adding the following to your .env file:
```
JWT_KEYS_DIRECTORY_PATH=/path/to/jwt/keys/directory
```

### Library installation
This command will install the library and all its dependencies.
```sh
composer require malvik-lab/laravel-jwt
```

### Publishing the configuration
This file contains the library configuration, such as the token signing method, token lifetime, and JWT key path.
```sh
php artisan vendor:publish --tag=jwt-config
```

### Migration publication
This file creates the jwt_tokens table in your database. This table is used to store issued JWT tokens.
```sh
php artisan vendor:publish --tag=jwt-migration
```

### Running the migration
This command will create the jwt_tokens table in your database.
```sh
php artisan migrate
```

### JWT key generation
this command will generate a public and private key pair, which will be used to sign and verify JWT tokens.
```sh
php artisan jwt:keys
```
### Add Guards
Add new guards to auth configuration file.
```php
<?php
## config/auth.php

return [
    // ...

    'guards' => [
        // ...
        
        'jwt-access-token' => [
            'driver' => 'jwt-access-token',
            'provider' => 'users',
        ],
    
        'jwt-refresh-token' => [
            'driver' => 'jwt-refresh-token',
            'provider' => 'users',
        ]
    ],

    // ...
];
```

### Enable API authentication with JWT
```php
<?php
## app/Providers/AuthServiceProvider.php

namespace App\Providers;

// ...
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Foundation\Application;
use MalvikLab\LaravelJwt\Http\Guards\JwtAccessTokenGuard;
use MalvikLab\LaravelJwt\Http\Guards\JwtRefreshTokenGuard;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // ...
    ];

    public function boot(): void
    {
        // ...
    
        Auth::extend('jwt-access-token', function (Application $app, string $name, array $config) {
            return new JwtAccessTokenGuard(Auth::createUserProvider($config['provider']));
        });

        Auth::extend('jwt-refresh-token', function (Application $app, string $name, array $config) {
            return new JwtRefreshTokenGuard(Auth::createUserProvider($config['provider']));
        });
    }
}
```

### Routes
To test JWT authentication, you can add routes to your project that allow you to authenticate with a valid JWT token.
```php
<?php
## app/routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use MalvikLab\LaravelJwt\Http\Controllers\AuthController;

Route::middleware(['guest'])->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
});

Route::middleware(['auth:jwt-access-token'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
});

Route::middleware(['auth:jwt-refresh-token'])->group(function () {
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
});
```


### Recommended but not mandatory
To ensure a JSON response is always returned, it is recommended to do the following:

Add render method

```php
<?php
## app/Exceptions/Handler.php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        // ...
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e): Response | JsonResponse | RedirectResponse |SymfonyResponse
    {
        if ( $request->is('api/*') )
        {
            $request->headers->set('accept', 'application/json');
        }

        return parent::render($request, $e);
    }
}
```

Add AcceptJson middleware
```php
<?php
## app/Http/Kernel.php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middleware = [
        // ...
    ];

    protected $middlewareGroups = [
        'web' => [
            // ...
        ],

        'api' => [
            \MalvikLab\LaravelJwt\Http\Middleware\AcceptJson::class,
            // ...
        ],
    ];

    // ...
}

```

### Installation complete
Now that you have installed the Laravel JWT library, you can start using its features. Here are some examples of requests and responses that you can use to test JWT authentication.

#### Login
Request
```http
POST /api/auth/login HTTP/1.1

{
    "email": "john.doe@example.com",
    "password": "123456789"
}
```

Response
```http
HTTP/1.1 200 OK
Content-Type: application/json

{
    "token_type": "Bearer",
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0Iiwic3ViIjoiOWEzZTI5NDktYzcwZS00ZmFmLWEzMDEtZWQ3MmVhOGM5NzM5IiwidG9rZW5fdHlwZSI6IkFDQ0VTU19UT0tFTiIsImlhdCI6MTY5NjA2ODE2OCwianRpIjoiMzc4OWFlZmUtMWUxZC00ODI3LWFjZjEtNWY1MWJkYTAzMWY5IiwiZXhwIjoxNjk2MDgyNTY4LCJ1c2VyIjp7ImlkIjoiOWEzZTI5NDktYzcwZS00ZmFmLWEzMDEtZWQ3MmVhOGM5NzM5IiwibmFtZSI6IkpvaG4gRG9lIiwiZW1haWwiOiJqb2huLmRvZUBleGFtcGxlLmNvbSJ9fQ.fhwqM01o6ieNoegkczJGdlB5xEcLyD6ZHWu-0avS7WZhUp5iUQLdF6_qMKpWvgiuiPYoPtxrAowG3SIbYakjYSr1pdnBrN9Pg2T4ONTqYO0VQiVCEYujvN-XHKcsG4xvkkdVxe2v75_nFPxnWxVFgg3xQZFmjuoUtpFWHf5TQSjIebDxMwO1wDseohI-8GlP69rGR-8KIWh9Ig_fPRz_Hsrjognhi8Q6vZpW4w3e0uW2xyCa4gpF-JfKHvR1qXRFyaZFD2MuP614U740Xk3Gbqc0YTzbNzYBWbivtcjZs8d6QobBE1-KJjqoDtHQ3RV9nuV0SSGvrABxpW4dFOcxWg",
    "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0Iiwic3ViIjoiOWEzZTI5NDktYzcwZS00ZmFmLWEzMDEtZWQ3MmVhOGM5NzM5IiwidG9rZW5fdHlwZSI6IlJFRlJFU0hfVE9LRU4iLCJpYXQiOjE2OTYwNjgxNjgsImp0aSI6IjRhNTBlOWE5LWQ2MzktNGZmNy05NWI2LTA2NzAxYjUxMjMzZCIsImV4cCI6MTY5ODY2MDE2OH0.A-keVY5H-smKTRGVlVWLosmftK4f-927tfPVKxALznumQN4L2Q3xN4bmi-6mkHp8XBZZvtKLdAXZzWCMRwz4_EvGg02lNbGnZoI7qQ-scHGu7zDc3Bbs-_FXCCYchrSijo-rtjAlAoD1c6ilr9VYXjOq6-QBAJvx10v-IjGZRcsXiveef7XFYwEz9rb605lQdvpNLOoulGT3R43BXlszbfObqWBz-WObBlL-AVPleiiHAbDNLRdisJhv-XyV1G4YKY_SLEMv7u8q09t5J6L1aj3Reya3bSm4JEJP6-3WZIUzIopaVhSVJd4SjHjr0F4iSDR73rQYtdaWYYtHRs3-YQ",
    "expire_in": 14400
}
```

#### Me
Request
```http
GET /api/auth/me HTTP/1.1

Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0Iiwic3ViIjoiOWEzZTI5NDktYzcwZS00ZmFmLWEzMDEtZWQ3MmVhOGM5NzM5IiwidG9rZW5fdHlwZSI6IkFDQ0VTU19UT0tFTiIsImlhdCI6MTY5NjA2ODE2OCwianRpIjoiMzc4OWFlZmUtMWUxZC00ODI3LWFjZjEtNWY1MWJkYTAzMWY5IiwiZXhwIjoxNjk2MDgyNTY4LCJ1c2VyIjp7ImlkIjoiOWEzZTI5NDktYzcwZS00ZmFmLWEzMDEtZWQ3MmVhOGM5NzM5IiwibmFtZSI6IkpvaG4gRG9lIiwiZW1haWwiOiJqb2huLmRvZUBleGFtcGxlLmNvbSJ9fQ.fhwqM01o6ieNoegkczJGdlB5xEcLyD6ZHWu-0avS7WZhUp5iUQLdF6_qMKpWvgiuiPYoPtxrAowG3SIbYakjYSr1pdnBrN9Pg2T4ONTqYO0VQiVCEYujvN-XHKcsG4xvkkdVxe2v75_nFPxnWxVFgg3xQZFmjuoUtpFWHf5TQSjIebDxMwO1wDseohI-8GlP69rGR-8KIWh9Ig_fPRz_Hsrjognhi8Q6vZpW4w3e0uW2xyCa4gpF-JfKHvR1qXRFyaZFD2MuP614U740Xk3Gbqc0YTzbNzYBWbivtcjZs8d6QobBE1-KJjqoDtHQ3RV9nuV0SSGvrABxpW4dFOcxWg

```

Response
```http
HTTP/1.1 200 OK
Content-Type: application/json

{
    "id": "9a3e2949-c70e-4faf-a301-ed72ea8c9739",
    "name": "John Doe",
    "email": "john.doe@example.com"
}
```

#### Refresh token
Request
```http
POST /api/auth/refresh HTTP/1.1

Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0Iiwic3ViIjoiOWEzZTI5NDktYzcwZS00ZmFmLWEzMDEtZWQ3MmVhOGM5NzM5IiwidG9rZW5fdHlwZSI6IlJFRlJFU0hfVE9LRU4iLCJpYXQiOjE2OTYwNjgxNjgsImp0aSI6IjRhNTBlOWE5LWQ2MzktNGZmNy05NWI2LTA2NzAxYjUxMjMzZCIsImV4cCI6MTY5ODY2MDE2OH0.A-keVY5H-smKTRGVlVWLosmftK4f-927tfPVKxALznumQN4L2Q3xN4bmi-6mkHp8XBZZvtKLdAXZzWCMRwz4_EvGg02lNbGnZoI7qQ-scHGu7zDc3Bbs-_FXCCYchrSijo-rtjAlAoD1c6ilr9VYXjOq6-QBAJvx10v-IjGZRcsXiveef7XFYwEz9rb605lQdvpNLOoulGT3R43BXlszbfObqWBz-WObBlL-AVPleiiHAbDNLRdisJhv-XyV1G4YKY_SLEMv7u8q09t5J6L1aj3Reya3bSm4JEJP6-3WZIUzIopaVhSVJd4SjHjr0F4iSDR73rQYtdaWYYtHRs3-YQ

```

Response
(Same as the login response)

#### Logout
Request
```http
POST /api/auth/logout HTTP/1.1

Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0Iiwic3ViIjoiOWEzZTI5NDktYzcwZS00ZmFmLWEzMDEtZWQ3MmVhOGM5NzM5IiwidG9rZW5fdHlwZSI6IkFDQ0VTU19UT0tFTiIsImlhdCI6MTY5NjA2ODE2OCwianRpIjoiMzc4OWFlZmUtMWUxZC00ODI3LWFjZjEtNWY1MWJkYTAzMWY5IiwiZXhwIjoxNjk2MDgyNTY4LCJ1c2VyIjp7ImlkIjoiOWEzZTI5NDktYzcwZS00ZmFmLWEzMDEtZWQ3MmVhOGM5NzM5IiwibmFtZSI6IkpvaG4gRG9lIiwiZW1haWwiOiJqb2huLmRvZUBleGFtcGxlLmNvbSJ9fQ.fhwqM01o6ieNoegkczJGdlB5xEcLyD6ZHWu-0avS7WZhUp5iUQLdF6_qMKpWvgiuiPYoPtxrAowG3SIbYakjYSr1pdnBrN9Pg2T4ONTqYO0VQiVCEYujvN-XHKcsG4xvkkdVxe2v75_nFPxnWxVFgg3xQZFmjuoUtpFWHf5TQSjIebDxMwO1wDseohI-8GlP69rGR-8KIWh9Ig_fPRz_Hsrjognhi8Q6vZpW4w3e0uW2xyCa4gpF-JfKHvR1qXRFyaZFD2MuP614U740Xk3Gbqc0YTzbNzYBWbivtcjZs8d6QobBE1-KJjqoDtHQ3RV9nuV0SSGvrABxpW4dFOcxWg

```

Response
```http
HTTP/1.1 204 No Content
```