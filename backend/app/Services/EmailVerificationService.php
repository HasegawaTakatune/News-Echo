<?php

namespace App\Services;

use App\Models\EmailVerificationToken;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EmailVerificationService
{
    public function sendToken(string $email): void
    {
        $token = strtoupper(Str::random(5));

        EmailVerificationToken::where('email', $email)->delete();
        EmailVerificationToken::create([
            'email' => $email,
            'token' => $token,
            'expires_at' => now()->addMinutes(15),
        ]);

        Mail::raw("メールアドレス確認用コード: {$token}\n\n15分以内に入力してください。", function ($message) use ($email) {
            $message->to($email)->subject('メールアドレス確認');
        });
    }

    public function verify(string $email, string $token): bool
    {
        $record = EmailVerificationToken::where('email', $email)
            ->where('token', strtoupper($token))
            ->where('expires_at', '>', now())
            ->first();

        if (!$record) {
            throw ValidationException::withMessages([
                'token' => ['確認コードが正しくないか、有効期限が切れています。'],
            ]);
        }

        $record->delete();
        return true;
    }
}
