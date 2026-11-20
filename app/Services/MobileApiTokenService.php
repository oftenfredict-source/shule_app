<?php

namespace App\Services;

use App\Models\MobileApiToken;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MobileApiTokenService
{
    public function issue(User $user, array $context = [], ?string $deviceName = null): array
    {
        $this->ensureTable();
        if (!$user->user_type) {
            $user = User::findOrFail($user->id);
        }
        $plain = Str::random(64);
        $expiresAt = now()->addDays(90);

        $token = MobileApiToken::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $plain),
            'user_type' => $user->user_type,
            'schoolID' => $context['schoolID'] ?? null,
            'parentID' => $context['parentID'] ?? null,
            'teacherID' => $context['teacherID'] ?? null,
            'device_name' => $deviceName ? Str::limit(trim($deviceName), 120, '') : null,
            'last_used_at' => now(),
            'expires_at' => $expiresAt,
        ]);

        $stale = MobileApiToken::where('user_id', $user->id)
            ->orderByDesc('id')
            ->skip(8)
            ->take(50)
            ->pluck('id');
        if ($stale->isNotEmpty()) {
            MobileApiToken::whereIn('id', $stale)->delete();
        }

        return [
            'token' => $plain,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt->toIso8601String(),
            'token_id' => $token->id,
        ];
    }

    public function findValid(string $plain): ?MobileApiToken
    {
        $plain = trim($plain);
        if ($plain === '' || !$this->tableExists()) {
            return null;
        }

        $token = MobileApiToken::with('user')
            ->where('token_hash', hash('sha256', $plain))
            ->first();

        if (!$token || !$token->user) {
            return null;
        }

        if ($token->expires_at && $token->expires_at->isPast()) {
            $token->delete();

            return null;
        }

        if (!$token->last_used_at || $token->last_used_at->lt(now()->subMinutes(15))) {
            $token->forceFill(['last_used_at' => now()])->save();
        }

        return $token;
    }

    public function extractBearer(Request $request): ?string
    {
        $header = (string) $request->header('Authorization', '');
        if ($header === '') {
            $header = (string) ($request->server('REDIRECT_HTTP_AUTHORIZATION')
                ?: $request->server('HTTP_AUTHORIZATION')
                ?: '');
        }
        if (preg_match('/^Bearer\s+(\S+)/i', $header, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public function revokeFromRequest(Request $request): void
    {
        $plain = $this->extractBearer($request);
        if (!$plain) {
            return;
        }

        if (!$this->tableExists()) {
            return;
        }

        MobileApiToken::where('token_hash', hash('sha256', $plain))->delete();
    }

    private function tableExists(): bool
    {
        return Schema::hasTable('mobile_api_tokens');
    }

    private function ensureTable(): void
    {
        if ($this->tableExists()) {
            return;
        }

        Schema::create('mobile_api_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('token_hash', 64)->unique();
            $table->string('user_type', 32);
            $table->unsignedBigInteger('schoolID')->nullable()->index();
            $table->unsignedBigInteger('parentID')->nullable()->index();
            $table->unsignedBigInteger('teacherID')->nullable();
            $table->string('device_name')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function revokeOtherTokens(int $userId, int $keepTokenId): void
    {
        MobileApiToken::where('user_id', $userId)
            ->where('id', '!=', $keepTokenId)
            ->delete();
    }
}
