<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnonymousCrush;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CrushController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $sent = AnonymousCrush::where('sender_id', $user->id)
            ->latest()->take(10)->get()
            ->map(fn($c) => [
                'id'                => $c->id,
                'target_identifier' => $c->target_identifier,
                'target_type'       => $c->target_type,
                'message'           => $c->message,
                'created_at'        => $c->created_at->diffForHumans(),
                'is_revealed'       => $c->is_revealed,
            ]);

        $received = AnonymousCrush::where('target_user_id', $user->id)
            ->with('sender.profile')
            ->latest()->get()
            ->map(fn($c) => [
                'id'                => $c->id,
                'message'           => $c->message,
                'sender_university' => $c->sender_university,
                'is_revealed'       => $c->is_revealed,
                'revealed_at'       => $c->revealed_at?->toISOString(),
                'sender'            => $c->is_revealed ? [
                    'id'       => $c->sender->id,
                    'name'     => $c->sender->name,
                    'photo_url' => $c->sender->profile?->photo_url,
                ] : null,
                'created_at'        => $c->created_at->diffForHumans(),
            ]);

        return response()->json([
            'sent'     => $sent,
            'received' => $received,
        ]);
    }

    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'target'  => 'required|string|max:255',
            'message' => 'nullable|string|max:200',
        ]);

        $user   = $request->user();
        $target = trim($request->target);

        $isEmail = filter_var($target, FILTER_VALIDATE_EMAIL);
        $isPhone = preg_match('/^(77|78|76|70|75)[0-9]{7}$/', $target);

        if (!$isEmail && !$isPhone) {
            return response()->json(['message' => 'Entre un email valide ou un numéro sénégalais (ex: 77XXXXXXX).'], 422);
        }

        if ($isEmail && $target === $user->email) {
            return response()->json(['message' => 'Tu ne peux pas t\'envoyer un crush à toi-même.'], 422);
        }

        $todayCount = AnonymousCrush::where('sender_id', $user->id)->whereDate('created_at', today())->count();
        if ($todayCount >= 5) {
            return response()->json(['message' => 'Limite de 5 crushes anonymes par jour atteinte.'], 429);
        }

        $existing = AnonymousCrush::where('sender_id', $user->id)->where('target_identifier', $target)->first();
        if ($existing) {
            return response()->json(['message' => 'Tu as déjà envoyé un crush à cette personne.'], 409);
        }

        $targetUser = $isEmail ? User::where('email', $target)->first() : null;
        $senderUniversity = $user->profile?->university_name ?? 'une université sénégalaise';

        $crush = AnonymousCrush::create([
            'sender_id'          => $user->id,
            'target_identifier'  => $target,
            'target_type'        => $isEmail ? 'email' : 'phone',
            'target_user_id'     => $targetUser?->id,
            'message'            => $request->message,
            'sender_university'  => $senderUniversity,
        ]);

        if ($targetUser) {
            try {
                app(\App\Services\WebPushService::class)->sendToUser(
                    $targetUser,
                    '👀 Crush anonyme !',
                    "Quelqu'un de {$senderUniversity} a un crush sur toi !",
                    '/crush'
                );
            } catch (\Exception) {}
        }

        return response()->json([
            'message'         => 'Crush anonyme envoyé ! 💘',
            'target_on_app'   => $targetUser !== null,
        ], 201);
    }

    public function reveal(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $crush = AnonymousCrush::where('id', $id)
            ->where('target_user_id', $user->id)
            ->where('is_revealed', false)
            ->firstOrFail();

        $crush->update(['is_revealed' => true, 'revealed_at' => now()]);

        $sender = $crush->sender;

        return response()->json([
            'message' => 'Crush révélé ! 💘',
            'sender'  => [
                'id'        => $sender->id,
                'name'      => $sender->name,
                'photo_url' => $sender->profile?->photo_url,
            ],
        ]);
    }
}
