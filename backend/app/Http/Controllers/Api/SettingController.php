<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SettingController extends Controller
{
    public function __construct(
        private SettingService $settingService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $data = [
            'user' => $request->user()->only(['id', 'name', 'email', 'is_admin']),
        ];
        if ($request->user()->is_admin) {
            $data['post_account'] = $this->settingService->getPostAccount();
            $data['research_prompt'] = $this->settingService->getResearchPrompt();
        }
        return response()->json($data);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $this->settingService->updatePassword(
                $request->user(),
                $validated['current_password'],
                $validated['password']
            );
            return response()->json(['message' => 'パスワードを更新しました。']);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => '更新に失敗しました。',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function updatePostAccount(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'post_account' => 'required|string|max:255',
        ]);

        $this->settingService->updatePostAccount($validated['post_account']);
        return response()->json(['message' => '投稿アカウントを変更しました。']);
    }

    public function updateResearchPrompt(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'research_prompt' => 'required|string|max:2000',
        ]);

        $this->settingService->updateResearchPrompt($validated['research_prompt']);
        return response()->json(['message' => 'リサーチプロンプトを変更しました。']);
    }
}
