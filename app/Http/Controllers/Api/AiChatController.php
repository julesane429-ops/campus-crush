<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiChatMessage;
use App\Models\AiChatSession;
use App\Models\Payment;
use App\Services\AiChatService;
use App\Services\PayDunyaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AiChatController extends Controller
{
    public function __construct(
        private AiChatService   $aiChat,
        private PayDunyaService $paydunya
    ) {}

    public function bots(Request $request): JsonResponse
    {
        $user    = $request->user();
        $profile = $user->profile;

        if (!$profile) {
            return response()->json(['message' => 'Profil requis.'], 403);
        }

        $bots       = AiChatService::getBots($profile->gender);
        $isUnlocked = $user->ai_chat_unlocked;

        $botsFormatted = collect($bots)->map(function ($bot, $type) use ($isUnlocked) {
            return [
                'type'        => $type,
                'name'        => $bot['name'],
                'description' => $bot['description'] ?? '',
                'avatar'      => $bot['avatar'] ?? null,
                'is_unlocked' => $type === 'support' || $isUnlocked,
            ];
        })->values();

        return response()->json([
            'bots'        => $botsFormatted,
            'is_unlocked' => $isUnlocked,
        ]);
    }

    public function session(Request $request, string $botType): JsonResponse
    {
        $user    = $request->user();
        $profile = $user->profile;

        if (!$profile) {
            return response()->json(['message' => 'Profil requis.'], 403);
        }

        if ($botType !== 'support' && !$user->ai_chat_unlocked) {
            return response()->json(['message' => 'Débloque l\'IA pour 500 FCFA.', 'error' => 'locked'], 403);
        }

        $bots = AiChatService::getBots($profile->gender);
        if (!isset($bots[$botType])) {
            return response()->json(['message' => 'Bot introuvable.'], 404);
        }

        $bot = $bots[$botType];
        $aiSession = AiChatSession::firstOrCreate(
            ['user_id' => $user->id, 'bot_type' => $botType, 'is_active' => true],
            ['bot_name' => $bot['name'], 'bot_avatar' => $bot['avatar'] ?? null]
        );

        $messages = AiChatMessage::where('session_id', $aiSession->id)->orderBy('created_at', 'asc')->get();

        if ($messages->isEmpty() && $botType !== 'support') {
            $welcomeTrigger = $this->getWelcomeTrigger($botType);
            $welcomeMsg     = $this->aiChat->chat($aiSession, $welcomeTrigger);
            AiChatMessage::create(['session_id' => $aiSession->id, 'role' => 'assistant', 'content' => $welcomeMsg]);
            $aiSession->increment('message_count');
            $messages = AiChatMessage::where('session_id', $aiSession->id)->orderBy('created_at', 'asc')->get();
        }

        return response()->json([
            'session_id' => $aiSession->id,
            'bot'        => [
                'type'   => $botType,
                'name'   => $bot['name'],
                'avatar' => $bot['avatar'] ?? null,
            ],
            'messages'   => $messages->map(fn($m) => [
                'id'         => $m->id,
                'role'       => $m->role,
                'content'    => $m->content,
                'created_at' => $m->created_at->toISOString(),
            ]),
        ]);
    }

    public function send(Request $request, int $sessionId): JsonResponse
    {
        $request->validate(['message' => 'required|string|max:500']);

        $user      = $request->user();
        $aiSession = AiChatSession::where('id', $sessionId)->where('user_id', $user->id)->firstOrFail();

        $todayCount = AiChatMessage::where('session_id', $aiSession->id)
            ->where('role', 'user')
            ->whereDate('created_at', today())
            ->count();

        if ($todayCount >= 50) {
            return response()->json(['reply' => 'Limite de 50 messages/jour atteinte. Reviens demain ! 😊', 'limited' => true]);
        }

        AiChatMessage::create(['session_id' => $aiSession->id, 'role' => 'user', 'content' => $request->message]);
        $reply = $this->aiChat->chat($aiSession, $request->message);
        AiChatMessage::create(['session_id' => $aiSession->id, 'role' => 'assistant', 'content' => $reply]);
        $aiSession->increment('message_count', 2);

        return response()->json(['reply' => $reply, 'limited' => false]);
    }

    public function reset(Request $request, int $sessionId): JsonResponse
    {
        $aiSession = AiChatSession::where('id', $sessionId)->where('user_id', $request->user()->id)->firstOrFail();
        $aiSession->messages()->delete();
        $aiSession->update(['message_count' => 0]);

        return response()->json(['message' => 'Conversation réinitialisée.']);
    }

    public function pay(Request $request): JsonResponse
    {
        $request->validate([
            'payment_method' => 'required|in:orange_money,wave,free_money',
            'phone_number'   => ['required', 'string', 'regex:/^(77|78|76|70|75)[0-9]{7}$/'],
        ]);

        if (!$this->paydunya->isPaymentMethodEnabled($request->payment_method)) {
            return response()->json(['message' => 'Wave est temporairement indisponible via PayDunya. Utilise Orange Money pour finaliser ton paiement.'], 422);
        }

        $user = $request->user();

        if ($user->ai_chat_unlocked) {
            return response()->json(['message' => 'IA déjà débloquée.'], 409);
        }

        $payment = Payment::create([
            'user_id'         => $user->id,
            'subscription_id' => $user->getOrCreateSubscription()->id,
            'amount'          => 500,
            'payment_method'  => $request->payment_method,
            'phone_number'    => $request->phone_number,
            'transaction_id'  => 'AI-' . strtoupper(Str::random(10)),
            'status'          => 'pending',
            'notes'           => 'ai_chat_unlock',
        ]);

        if (!empty(config('paydunya.master_key'))) {
            $result = $this->paydunya->payDirect(
                $user->id, $user->name, $user->email,
                $request->phone_number, $request->payment_method,
                500, 'Déblocage IA Campus Crush',
                config('app.url') . '/api/ai/pay/success',
                config('app.url') . '/api/ai/bots',
                'ai_chat'
            );

            if ($result['success']) {
                $payment->update(['transaction_id' => $result['token'], 'notes' => 'ai_chat|' . $result['method']]);

                return response()->json([
                    'token'     => $result['token'],
                    'method'    => $result['method'],
                    'url'       => $result['url'] ?? null,
                    'om_url'    => $result['om_url'] ?? null,
                    'maxit_url' => $result['maxit_url'] ?? null,
                    'message'   => $result['message'] ?? null,
                ]);
            }

            $payment->update(['status' => 'failed', 'notes' => $result['error']]);
            return response()->json(['message' => 'Erreur : ' . $result['error']], 422);
        }

        // Mode simulation
        $payment->update(['status' => 'completed']);
        $user->update(['ai_chat_unlocked' => true, 'ai_chat_unlocked_at' => now()]);

        return response()->json(['method' => 'simulation', 'message' => 'IA débloquée (simulation) !']);
    }

    private function getWelcomeTrigger(string $botType): string
    {
        return match ($botType) {
            'match_girl', 'match_boy' => 'Salut ! (envoie ton premier message comme si on venait de matcher)',
            'coach'                   => 'Analyse mon profil et donne-moi tes premiers conseils.',
            'flirt'                   => 'Salut ! (commence la conversation d\'entraînement)',
            default                   => 'Bonjour !',
        };
    }
}
