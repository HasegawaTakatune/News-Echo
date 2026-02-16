<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EmailVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EmailVerificationController extends Controller
{
    public function __construct(
        private EmailVerificationService $emailVerificationService
    ) {}

    public function sendToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $this->emailVerificationService->sendToken($validated['email']);
            return response()->json(['message' => '確認コードを送信しました。']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '送信に失敗しました。',
                'errors' => ['email' => [$e->getMessage()]],
            ], 422);
        }
    }

    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'token' => 'required|string|size:5',
        ]);

        try {
            $this->emailVerificationService->verify(
                $validated['email'],
                $validated['token']
            );
            return response()->json(['message' => 'メールアドレスを確認しました。']);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => '確認に失敗しました。',
                'errors' => $e->errors(),
            ], 422);
        }
    }
}
