<?php

namespace App\Http\Controllers;

use App\Models\AiChatSession;
use App\Models\AiChatMessage;
use App\Models\Payment;
use App\Services\AiChatService;
use App\Services\PayDunyaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiChatController extends Controller
{
    public function __construct(
        private AiChatService $aiChat,
        private PayDunyaService $paydunya
    ) {}

    /**
     * Page principale — liste des bots.
     */
    public function index()
    {
        $user = Auth::user();
        $profile = $user->profile;
        if (!$profile) return redirect()->route('profile.create');

        $bots = AiChatService::getBots($profile->gender);
        $isUnlocked = $user->ai_chat_unlocked;

        // Sessions actives
        $sessions = AiChatSession::where('user_id', $user->id)
            ->where('is_active', true)
            ->with(['messages' => fn($q) => $q->latest()->take(1)])
            ->get()
            ->keyBy('bot_type');

        return view('ai-chat.index', compact('bots', 'isUnlocked', 'sessions'));
    }

    /**
     * Ouvrir/créer une session de chat avec un bot.
     */
    public function session(string $botType)
    {
        $user = Auth::user();
        $profile = $user->profile;

        // Support est gratuit, les autres nécessitent le paiement
        if ($botType !== 'support' && !$user->ai_chat_unlocked) {
            return redirect()->route('ai.index')->with('error', 'Débloque l\'IA Campus Crush pour 500 FCFA pour accéder à cette fonctionnalité.');
        }

        $bots = AiChatService::getBots($profile->gender);
        if (!isset($bots[$botType])) abort(404);
        $bot = $bots[$botType];

        // Trouver ou créer la session
        $session = AiChatSession::firstOrCreate(
            ['user_id' => $user->id, 'bot_type' => $botType, 'is_active' => true],
            ['bot_name' => $bot['name'], 'bot_avatar' => $bot['avatar']]
        );

        // Charger les messages
        $messages = AiChatMessage::where('session_id', $session->id)
            ->orderBy('created_at', 'asc')
            ->get();

        // Si première fois, générer le message d'accueil
        if ($messages->isEmpty() && $botType !== 'support') {
            $welcomeMsg = $this->aiChat->chat($session, $this->getWelcomeTrigger($botType));

            AiChatMessage::create([
                'session_id' => $session->id,
                'role' => 'assistant',
                'content' => $welcomeMsg,
            ]);

            $session->increment('message_count');
            $messages = AiChatMessage::where('session_id', $session->id)->orderBy('created_at', 'asc')->get();
        }

        return view('ai-chat.session', compact('session', 'messages', 'bot', 'botType'));
    }

    /**
     * Envoyer un message au bot.
     */
    public function send(Request $request, int $sessionId)
    {
        $request->validate(['message' => 'required|string|max:500']);

        $user = Auth::user();
        $session = AiChatSession::where('id', $sessionId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Limite : 50 messages/jour (contrôle des coûts)
        $todayCount = AiChatMessage::where('session_id', $session->id)
            ->where('role', 'user')
            ->whereDate('created_at', today())
            ->count();

        if ($todayCount >= 50) {
            return response()->json([
                'reply' => 'Tu as atteint la limite de 50 messages par jour. Reviens demain ! 😊',
                'limited' => true,
            ]);
        }

        // Sauvegarder le message utilisateur
        AiChatMessage::create([
            'session_id' => $session->id,
            'role' => 'user',
            'content' => $request->message,
        ]);

        // Obtenir la réponse IA
        $reply = $this->aiChat->chat($session, $request->message);

        // Sauvegarder la réponse
        AiChatMessage::create([
            'session_id' => $session->id,
            'role' => 'assistant',
            'content' => $reply,
        ]);

        $session->increment('message_count', 2);

        return response()->json([
            'reply' => $reply,
            'limited' => false,
        ]);
    }

    /**
     * Réinitialiser une session (nouvelle conversation).
     */
    public function reset(int $sessionId)
    {
        $user = Auth::user();
        $session = AiChatSession::where('id', $sessionId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $session->messages()->delete();
        $session->update(['message_count' => 0]);

        return back()->with('success', 'Conversation réinitialisée !');
    }

    /**
     * Page de paiement pour débloquer l'IA.
     */
    public function unlock()
    {
        $user = Auth::user();
        if ($user->ai_chat_unlocked) {
            return redirect()->route('ai.index')->with('success', 'IA déjà débloquée !');
        }
        return view('ai-chat.unlock');
    }

    /**
     * Payer pour débloquer l'IA.
     */
    public function pay(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:orange_money,wave,free_money',
            'phone_number' => ['required', 'string', 'regex:/^(77|78|76|70|75)[0-9]{7}$/'],
        ]);

        $user = Auth::user();

        if ($user->ai_chat_unlocked) {
            return redirect()->route('ai.index');
        }

        $payment = Payment::create([
            'user_id' => $user->id,
            'subscription_id' => $user->getOrCreateSubscription()->id,
            'amount' => 500,
            'payment_method' => $request->payment_method,
            'phone_number' => $request->phone_number,
            'transaction_id' => 'AI-' . strtoupper(Str::random(10)),
            'status' => 'pending',
            'notes' => 'ai_chat_unlock',
        ]);

        // PayDunya
        if (!empty(config('paydunya.master_key'))) {
            $result = $this->paydunya->createAiChatInvoice(
                $user->id,
                $user->name,
                $user->email,
                $request->phone_number,
                $request->payment_method
            );

            if ($result['success']) {
                $payment->update([
                    'transaction_id' => $result['token'],
                    'notes' => 'ai_chat_unlock|paydunya_redirect',
                ]);
                return redirect()->away($result['url']);
            }

            $payment->update(['status' => 'failed', 'notes' => $result['error']]);
            return back()->with('error', 'Erreur de paiement : ' . $result['error']);
        }

        // Mode simulation
        $payment->update(['status' => 'completed']);
        $user->update(['ai_chat_unlocked' => true, 'ai_chat_unlocked_at' => now()]);

        return redirect()->route('ai.index')->with('success', 'IA Campus Crush débloquée ! 🎉');
    }

    /**
     * Retour PayDunya après paiement.
     */
    public function paySuccess(Request $request)
    {
        $user = Auth::user();

        if ($token = $request->query('token')) {
            $result = $this->paydunya->checkPaymentStatus($token);

            if ($result['success'] && $result['status'] === 'completed') {
                $payment = Payment::where('transaction_id', $token)->first();
                if ($payment && $payment->status !== 'completed') {
                    $payment->update(['status' => 'completed']);
                    $user->update(['ai_chat_unlocked' => true, 'ai_chat_unlocked_at' => now()]);
                }
            }
        }

        return redirect()->route('ai.index')->with('success', 'IA Campus Crush débloquée ! 🎉');
    }

    private function getWelcomeTrigger(string $botType): string
    {
        return match ($botType) {
            'match_girl', 'match_boy' => 'Salut ! (envoie ton premier message comme si on venait de matcher)',
            'coach' => 'Analyse mon profil et donne-moi tes premiers conseils.',
            'flirt' => 'Salut ! (commence la conversation d\'entraînement)',
            default => 'Bonjour !',
        };
    }
}
