<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    use ResponseTrait;

    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * Register a new user with freelancer profile.
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $result = $this->authService->register($request->validated());

            DB::commit();

            return $this->successResponse([
                'message' => 'User registered successfully',
                'user' => $result['user'],
                'token' => $result['token'],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Register Error: ' . $e->getMessage());
            return $this->internalServerErrorResponse('Registration failed. Please try again.');
        }
    }

    /**
     * Login a user.
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $result = $this->authService->login(
                $request->validated('email'),
                $request->validated('password')
            );

            DB::commit();

            return $this->successResponse([
                'message' => 'Login successful',
                'user' => $result['user'],
                'token' => $result['token'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Login Error: ' . $e->getMessage());
            return $this->internalServerErrorResponse('Login failed. Please try again.');
        }
    }

    /**
     * Logout the current user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $this->authService->logout($request->user());

            if ($request->hasSession()) {
                \Illuminate\Support\Facades\Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken(); 
            }

            DB::commit();

            return $this->successResponseMessage('Logout successful');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Logout Error: ' . $e->getMessage());
            return $this->internalServerErrorResponse('Logout failed.');
        }
    }
}
