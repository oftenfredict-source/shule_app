<?php

namespace App\Http\Controllers;

use App\Http\Requests\AiSupportChatRequest;
use App\Services\AiSupportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use RuntimeException;

class AiSupportController extends Controller
{
    public function __construct(
        private AiSupportService $aiSupport
    ) {
    }

    public function bootstrap(): JsonResponse
    {
        $schoolId = (int) Session::get('schoolID');
        $userId = (int) Session::get('userID');
        $locale = Session::get('locale', 'en') === 'sw' ? 'sw' : 'en';

        return response()->json([
            'success' => true,
            'data' => [
                'assistant_name' => 'ShuleXpert AI Assistant',
                'locale' => $locale,
                'configured' => (bool) config('services.openai.key'),
                'conversations' => $this->aiSupport->listConversations($schoolId, $userId),
                'welcome' => $locale === 'sw'
                    ? 'Habari! Mimi ni ShuleXpert AI Assistant. Naweza kukusaidia kuhusu jinsi ya kutumia mfumo. Uliza swali lolote.'
                    : 'Hello! I am ShuleXpert AI Assistant. I can help you use the system. Ask me anything about ShuleXpert.',
            ],
        ]);
    }

    public function conversations(): JsonResponse
    {
        $schoolId = (int) Session::get('schoolID');
        $userId = (int) Session::get('userID');

        return response()->json([
            'success' => true,
            'data' => $this->aiSupport->listConversations($schoolId, $userId),
        ]);
    }

    public function storeConversation(Request $request): JsonResponse
    {
        $locale = $request->input('locale', Session::get('locale', 'en'));
        $locale = $locale === 'sw' ? 'sw' : 'en';

        $conversation = $this->aiSupport->createConversation(
            (int) Session::get('schoolID'),
            (int) Session::get('userID'),
            (string) Session::get('user_type'),
            $locale
        );

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
            $messages = $this->aiSupport->getConversationMessages(
                (int) Session::get('schoolID'),
                (int) Session::get('userID'),
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
        $locale = $request->input('locale', Session::get('locale', 'en'));
        $locale = $locale === 'sw' ? 'sw' : 'en';

        try {
            $result = $this->aiSupport->sendMessage(
                (int) Session::get('schoolID'),
                (int) Session::get('userID'),
                (string) Session::get('user_type'),
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
}
