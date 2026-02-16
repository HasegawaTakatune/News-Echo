<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        try {
            $result = $this->authService->login(
                $validated['login'],
                $validated['password']
            );
            return response()->json($result);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'ログインに失敗しました。',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $result = $this->authService->register($validated);
            return response()->json($result, 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '登録に失敗しました。',
                'errors' => ['email' => [$e->getMessage()]],
            ], 422);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());
        return response()->json(['message' => 'ログアウトしました。']);
    }
}
