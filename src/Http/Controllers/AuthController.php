<?php

namespace MalvikLab\LaravelJwt\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use MalvikLab\LaravelJwt\Http\Requests\LoginRequest;

class AuthController extends Controller
{
    private Guard $accessTokenGuard;
    private Guard $refreshTokenGuard;

    public function __construct()
    {
        $this->accessTokenGuard = Auth::guard('jwt-access-token');
        $this->refreshTokenGuard = Auth::guard('jwt-refresh-token');
    }

    /**
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $this->accessTokenGuard->attempt($request->validated());

        return $this->accessTokenGuard->response();
    }

    /**
     * @return Application | ResponseFactory | Response
     */
    public function logout(): Application | ResponseFactory | Response
    {
        $this->accessTokenGuard->logout();

        return response(null, SymfonyResponse::HTTP_NO_CONTENT);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    /**
     * @return mixed
     */
    public function refresh(): mixed
    {
        $this->refreshTokenGuard->refresh();

        return $this->refreshTokenGuard->response();
    }
}
