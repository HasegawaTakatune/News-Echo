<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SettingService
{
    public function updatePassword($user, string $currentPassword, string $newPassword): void
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['現在のパスワードが正しくありません。'],
            ]);
        }

        $user->update(['password' => Hash::make($newPassword)]);
    }

    public function updatePostAccount(string $value): void
    {
        Setting::set('post_account', $value);
    }

    public function updateResearchPrompt(string $value): void
    {
        Setting::set('research_prompt', $value);
    }

    public function getPostAccount(): ?string
    {
        return Setting::get('post_account');
    }

    public function getResearchPrompt(): ?string
    {
        return Setting::get('research_prompt');
    }
}
