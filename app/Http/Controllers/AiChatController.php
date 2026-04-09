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

    public function index()
    {
        $user = Auth::user();
        $profile = $user->profile;
        if (!$profile) return redirect()->route('profile.create');

        $bots = AiChatService::getBots($profile->gender);
        $isUnlocked = $user->ai_chat_unlocked;

        $sessions = AiChatSession::where('user_id', $user->id)
            ->where('is_active', true)
            ->with(['messages' => fn($q) => $q->latest()->take(1)])
            ->get()->keyBy('bot_type');

        return view('ai-chat.index', compact('bots', 'isUnlocked', 'sessions'));
    }

    public function session(string $botType)
    {
        $user = Auth::user();
        $profile = $user->profile;

        if ($botType !== 'support' && !$user->ai_chat_unlocked) {
            return redirect()->route('ai.index')->with('error', 'Débloque l\'IA pour 500 FCFA.');
        }

        $bots = AiChatService::getBots($profile->gender);
        if (!isset($bots[$botType])) abort(404);
        $bot = $bots[$botType];

        $session = AiChatSession::firstOrCreate(
            ['user_id' => $user->id, 'bot_type' => $botType, 'is_active' => true],
            ['bot_name' => $bot['name'], 'bot_avatar' => $bot['avatar']]
        );

        $messages = AiChatMessage::where('session_id', $session->id)->orderBy('created_at', 'asc')->get();

        if ($messages->isEmpty() && $botType !== 'support') {
            $welcomeMsg = $this->aiChat->chat($session, $this->getWelcomeTrigger($botType));
            AiChatMessage::create(['session_id' => $session->id, 'role' => 'assistant', 'content' => $welcomeMsg]);
            $session->increment('message_count');
            $messages = AiChatMessage::where('session_id', $session->id)->orderBy('created_at', 'asc')->get();
        }

        return view('ai-chat.session', compact('session', 'messages', 'bot', 'botType'));
    }

    public function send(Request $request, int $sessionId)
    {
        $request->validate(['message' => 'required|string|max:500']);
        $user = Auth::user();
        $session = AiChatSession::where('id', $sessionId)->where('user_id', $user->id)->firstOrFail();

        $todayCount = AiChatMessage::where('session_id', $session->id)->where('role', 'user')->whereDate('created_at', today())->count();
        if ($todayCount >= 50) {
            return response()->json(['reply' => 'Limite de 50 messages/jour atteinte. Reviens demain ! 😊', 'limited' => true]);
        }

        AiChatMessage::create(['session_id' => $session->id, 'role' => 'user', 'content' => $request->message]);
        $reply = $this->aiChat->chat($session, $request->message);
        AiChatMessage::create(['session_id' => $session->id, 'role' => 'assistant', 'content' => $reply]);
        $session->increment('message_count', 2);

        return response()->json(['reply' => $reply, 'limited' => false]);
    }

    public function reset(int $sessionId)
    {
        $session = AiChatSession::where('id', $sessionId)->where('user_id', Auth::id())->firstOrFail();
        $session->messages()->delete();
        $session->update(['message_count' => 0]);
        return back()->with('success', 'Conversation réinitialisée !');
    }

    public function unlock()
    {
        if (Auth::user()->ai_chat_unlocked) return redirect()->route('ai.index');
        return view('ai-chat.unlock');
    }

    public function pay(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:orange_money,wave,free_money',
            'phone_number' => ['required', 'string', 'regex:/^(77|78|76|70|75)[0-9]{7}$/'],
        ]);

        $user = Auth::user();
        if ($user->ai_chat_unlocked) return redirect()->route('ai.index');

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

        if (!empty(config('paydunya.master_key'))) {
            $result = $this->paydunya->payDirect(
                $user->id, $user->name, $user->email,
                $request->phone_number, $request->payment_method,
                500, 'Déblocage IA Campus Crush',
                route('ai.pay.success'), route('ai.unlock'), 'ai_chat'
            );

            if ($result['success']) {
                $payment->update(['transaction_id' => $result['token'], 'notes' => 'ai_chat|' . $result['method']]);

                if (in_array($result['method'], ['wave_redirect', 'om_redirect']) && $result['url']) {
                    return view('payment.waiting', [
                        'token'          => $result['token'],
                        'paymentMethod'  => $request->payment_method,
                        'amount'         => 500,
                        'method'         => $result['method'],
                        'softpayMessage' => null,
                        'redirectUrl'    => $result['url'],
                        'omUrl'          => $result['om_url']    ?? null,
                        'maxitUrl'       => $result['maxit_url'] ?? null,
                        'successUrl'     => route('ai.pay.success', ['token' => $result['token']]),
                        'cancelUrl'      => route('ai.unlock'),
                    ]);
                }

                if ($result['method'] === 'free_ussd') {
                    return view('payment.waiting', [
                        'token' => $result['token'],
                        'paymentMethod' => $request->payment_method,
                        'amount' => 500,
                        'method' => 'free_ussd',
                        'softpayMessage' => $result['message'],
                        'redirectUrl' => null,
                        'successUrl' => route('ai.pay.success', ['token' => $result['token']]),
                        'cancelUrl' => route('ai.unlock'),
                    ]);
                }

                if ($result['method'] === 'fallback_redirect' && $result['url']) {
                    return redirect()->away($result['url']);
                }
            }

            $payment->update(['status' => 'failed', 'notes' => $result['error']]);
            return back()->with('error', 'Erreur : ' . $result['error']);
        }

        $payment->update(['status' => 'completed']);
        $user->update(['ai_chat_unlocked' => true, 'ai_chat_unlocked_at' => now()]);
        return redirect()->route('ai.index')->with('success', 'IA débloquée ! 🎉');
    }

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
        return redirect()->route('ai.index')->with('success', 'IA débloquée ! 🎉');
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