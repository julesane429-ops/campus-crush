<?php

namespace App\Http\Controllers;

use App\Models\AnonymousCrush;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnonymousCrushController extends Controller
{
    /**
     * Page d'envoi de crush anonyme.
     */
    public function index()
    {
        $user = Auth::user();
        $sentCrushes = AnonymousCrush::where('sender_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        $receivedCrushes = AnonymousCrush::where('target_user_id', $user->id)
            ->with('sender.profile')
            ->latest()
            ->get();

        return view('crush.index', compact('sentCrushes', 'receivedCrushes'));
    }

    /**
     * Envoyer un crush anonyme.
     */
    public function send(Request $request)
    {
        $request->validate([
            'target' => 'required|string|max:255',
            'message' => 'nullable|string|max:200',
        ]);

        $user = Auth::user();
        $target = trim($request->target);

        // Détecter si c'est un email ou un téléphone
        $isEmail = filter_var($target, FILTER_VALIDATE_EMAIL);
        $isPhone = preg_match('/^(77|78|76|70|75)[0-9]{7}$/', $target);

        if (!$isEmail && !$isPhone) {
            return back()->with('error', 'Entre un email valide ou un numéro sénégalais (ex: 77XXXXXXX).');
        }

        // Empêcher de s'envoyer un crush à soi-même
        if ($isEmail && $target === $user->email) {
            return back()->with('error', 'Tu ne peux pas t\'envoyer un crush à toi-même 😄');
        }

        // Limiter à 5 crushes par jour
        $todayCount = AnonymousCrush::where('sender_id', $user->id)
            ->whereDate('created_at', today())
            ->count();

        if ($todayCount >= 5) {
            return back()->with('error', 'Tu as atteint la limite de 5 crushes anonymes par jour.');
        }

        // Vérifier si déjà envoyé à cette personne
        $existing = AnonymousCrush::where('sender_id', $user->id)
            ->where('target_identifier', $target)
            ->first();

        if ($existing) {
            return back()->with('error', 'Tu as déjà envoyé un crush à cette personne !');
        }

        // Chercher si la cible est déjà inscrite
        $targetUser = null;
        if ($isEmail) {
            $targetUser = User::where('email', $target)->first();
        }

        $senderUniversity = $user->profile?->university_name ?? 'une université sénégalaise';

        $crush = AnonymousCrush::create([
            'sender_id' => $user->id,
            'target_identifier' => $target,
            'target_type' => $isEmail ? 'email' : 'phone',
            'target_user_id' => $targetUser?->id,
            'message' => $request->message,
            'sender_university' => $senderUniversity,
        ]);

        // Si la cible est inscrite → push notification
        if ($targetUser) {
            try {
                app(\App\Services\WebPushService::class)->sendToUser(
                    $targetUser,
                    '👀 Crush anonyme !',
                    "Quelqu'un de {$senderUniversity} a un crush sur toi ! Ouvre l'app pour voir les indices.",
                    '/crush'
                );
            } catch (\Exception $e) {
                // Pas grave si le push échoue
            }
        }

        return back()->with('success', 'Crush anonyme envoyé ! 💘 ' . ($targetUser ? 'Cette personne est déjà sur Campus Crush.' : 'Cette personne n\'est pas encore inscrite. Elle verra ton crush en s\'inscrivant !'));
    }

    /**
     * Révéler un crush (la cible voit qui l'a envoyé).
     * Coûte un like automatique vers le sender.
     */
    public function reveal(int $id)
    {
        $user = Auth::user();
        $crush = AnonymousCrush::where('id', $id)
            ->where('target_user_id', $user->id)
            ->where('is_revealed', false)
            ->firstOrFail();

        $crush->update([
            'is_revealed' => true,
            'revealed_at' => now(),
        ]);

        return back()->with('success', 'Crush révélé ! Tu peux maintenant liker cette personne dans le swipe 💘');
    }
}
