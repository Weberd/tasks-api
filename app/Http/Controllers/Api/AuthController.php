<?php

namespace App\Http\Controllers\Api;

use App\Dto\User\UserCreateDto;
use App\Dto\User\UserLoginDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\Contracts\Contracts\UserServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly UserServiceInterface $userService
    )
    {
    }

    /**
     * Регистрация нового пользователя
     */
    public function register(UserRegisterRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $registerDto = new UserCreateDto(
                $validated['name'],
                $validated['email'],
                $validated['password'],
            );

            $user = $this->userService->register($registerDto);

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'user' => new UserResource($user),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
            ], 500);
        }
    }

    /**
     * Вход пользователя
     */
    public function login(UserLoginRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $userLoginDto = new UserLoginDto(
                $validated['email'],
                $validated['password'],
            );

            if ($this->userService->login($userLoginDto)) {
                $userDto = $this->userService->get($userLoginDto->email);

                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'user' => new UserResource($userDto),
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials',
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
            ], 500);
        }
    }

    /**
     * Выход пользователя
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $this->userService->logout($request->user());

            return response()->json([
                'success' => true,
                'message' => 'Logout successful',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
            ], 500);
        }
    }
}
