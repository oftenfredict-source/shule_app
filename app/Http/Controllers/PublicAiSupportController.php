<?php

namespace App\Http\Controllers;

use App\Http\Requests\AiSupportChatRequest;
use App\Services\AiSupportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use RuntimeException;

class PublicAiSupportController extends Controller
{
    public function __construct(
        private AiSupportService $aiSupport
    ) {
    }

    public function bootstrap(): JsonResponse
    {
        if (!(bool) config('services.openai.enabled', true)) {
            return response()->json([
                'success' => false,
                'message' => 'AI Customer Support is currently disabled.',
            ], 503);
        }

        $locale = app()->getLocale() === 'sw' || Session::get('locale') === 'sw' ? 'sw' : 'en';
        $token = $this->guestToken();

        return response()->json([
            'success' => true,
            'data' => [
                'assistant_name' => 'ShuleXpert AI Assistant',
                'locale' => $locale,
                'configured' => (bool) config('services.openai.key'),
                'conversations' => $this->aiSupport->listGuestConversations($token),
                'welcome' => $locale === 'sw'
                    ? 'Habari! Mimi ni ShuleXpert AI Assistant. Naweza kukusaidia kuhusu ShuleXpert. Uliza swali lolote.'
                    : 'Hello! I am ShuleXpert AI Assistant. I can help you learn about ShuleXpert. Ask me anything.',
            ],
        ]);
    }

    public function storeConversation(Request $request): JsonResponse
    {
        if (!(bool) config('services.openai.enabled', true)) {
            return response()->json([
                'success' => false,
                'message' => 'AI Customer Support is currently disabled.',
            ], 503);
        }

        $locale = $request->input('locale', Session::get('locale', app()->getLocale()));
        $locale = $locale === 'sw' ? 'sw' : 'en';

        $conversation = $this->aiSupport->createGuestConversation($this->guestToken(), $locale);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'status' => $conversation->status,
                'locale' => $conversation->locale,
            ],
        ]);
    }

    public function messages(int $conversation): JsonResponse
    {
        try {
            $messages = $this->aiSupport->getGuestConversationMessages(
                $this->guestToken(),
                $conversation
            );
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }

    public function chat(AiSupportChatRequest $request): JsonResponse
    {
        if (!(bool) config('services.openai.enabled', true)) {
            return response()->json([
                'success' => false,
                'message' => 'AI Customer Support is currently disabled.',
            ], 503);
        }

        $locale = $request->input('locale', Session::get('locale', app()->getLocale()));
        $locale = $locale === 'sw' ? 'sw' : 'en';

        try {
            $result = $this->aiSupport->sendGuestMessage(
                $this->guestToken(),
                (string) $request->ip(),
                (string) $request->input('message'),
                $request->filled('conversation_id') ? (int) $request->input('conversation_id') : null,
                $locale
            );
        } catch (RuntimeException $e) {
            $status = str_contains(strtolower($e->getMessage()), 'too many') ? 429 : 422;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $status);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'conversation_id' => $result['conversation']->id,
                'conversation_title' => $result['conversation']->title,
                'user_message' => [
                    'id' => $result['user_message']->id,
                    'role' => $result['user_message']->role,
                    'content' => $result['user_message']->content,
                    'created_at' => optional($result['user_message']->created_at)->toIso8601String(),
                ],
                'assistant_message' => [
                    'id' => $result['assistant_message']->id,
                    'role' => $result['assistant_message']->role,
                    'content' => $result['assistant_message']->content,
                    'created_at' => optional($result['assistant_message']->created_at)->toIso8601String(),
                ],
            ],
        ]);
    }

    private function guestToken(): string
    {
        $token = Session::get('ai_guest_token');
        if (!is_string($token) || strlen($token) < 20) {
            $token = Str::random(40);
            Session::put('ai_guest_token', $token);
        }

        return $token;
    }
}
