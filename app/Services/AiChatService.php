<?php

namespace App\Services;

use App\Models\AiChatSession;
use App\Models\AiChatMessage;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatService
{
    private string $apiKey;
    private string $model;
    private int $maxTokens;

    public function __construct()
    {
        $this->apiKey = config('services.openai.key', env('OPENAI_API_KEY', ''));
        $this->model = config('services.openai.model', 'gpt-4o-mini'); // Modèle économique
        $this->maxTokens = 300; // Réponses courtes pour contrôler les coûts
    }

    /**
     * Envoyer un message et obtenir la réponse IA.
     */
    public function chat(AiChatSession $session, string $userMessage): string
    {
        $user = $session->user;
        $profile = $user->profile;

        // Construire le système prompt selon le type de bot
        $systemPrompt = $this->getSystemPrompt($session->bot_type, $user, $profile);

        // Charger l'historique (derniers 20 messages pour limiter les tokens)
        $history = AiChatMessage::where('session_id', $session->id)
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get()
            ->reverse()
            ->values();

        // Construire les messages pour l'API
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        foreach ($history as $msg) {
            $messages[] = ['role' => $msg->role, 'content' => $msg->content];
        }

        $messages[] = ['role' => 'user', 'content' => $userMessage];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => $messages,
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->getTemperature($session->bot_type),
                'presence_penalty' => 0.6,
                'frequency_penalty' => 0.3,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['choices'][0]['message']['content'] ?? 'Désolé, je n\'ai pas compris. Réessaie 😊';
            }

            // 🔧 DEBUG TEMPORAIRE — à supprimer après diagnostic
            $errorBody = $response->json();
            $errorMsg  = $errorBody['error']['message'] ?? $response->body();
            Log::error('OpenAI API error', ['status' => $response->status(), 'body' => $response->body()]);
            return '[DEBUG ' . $response->status() . '] ' . $errorMsg;

        } catch (\Exception $e) {
            Log::error('OpenAI exception', ['message' => $e->getMessage()]);
            // 🔧 DEBUG TEMPORAIRE — à supprimer après diagnostic
            return '[DEBUG EXCEPTION] ' . $e->getMessage();
        }
    }

    /**
     * Température selon le type de bot.
     */
    private function getTemperature(string $botType): float
    {
        return match ($botType) {
            'support' => 0.3, // Précis et factuel
            'coach' => 0.5,   // Conseils structurés
            'match_girl', 'match_boy' => 0.8, // Naturel et varié
            'flirt' => 0.85,  // Créatif et fun
            default => 0.7,
        };
    }

    /**
     * Prompt système selon le type de bot.
     */
    private function getSystemPrompt(string $botType, User $user, $profile): string
    {
        $userName = $user->name;
        $userGender = $profile?->gender ?? 'inconnu';
        $userAge = $profile?->age ?? '';
        $userUniv = $profile?->university_name ?? 'université';
        $userUfr = $profile?->ufr ?? '';
        $userBio = $profile?->bio ?? '';
        $userInterests = $profile?->interests ?? '';

        return match ($botType) {

            // ══════════════════════════════════════
            // SUPPORT — Assistant Campus Crush
            // ══════════════════════════════════════
            'support' => <<<PROMPT
Tu es l'assistant officiel de Campus Crush, l'application de rencontres pour étudiants sénégalais.

Règles :
- Réponds TOUJOURS en français (ou en wolof si l'utilisateur parle wolof)
- Réponses courtes (2-4 phrases max)
- Sois amical, utilise des emojis avec modération
- Guide l'utilisateur pas à pas
- Ne donne JAMAIS de conseils médicaux ou juridiques
- Si tu ne sais pas, dis "Contacte notre support sur WhatsApp"

Fonctionnalités de l'app que tu connais :
- Swipe : glisse à droite pour liker, à gauche pour passer
- Match : quand deux personnes se likent mutuellement
- Chat : messagerie après un match
- Boost : 500 FCFA pour être vu en premier pendant 24h
- Crush anonyme : envoyer un crush sans se dévoiler
- Parrainage : inviter un ami = 7 jours premium gratuit
- Abonnement : 1000 FCFA/mois, premier mois gratuit
- Paiement : Orange Money, Wave ou Free Money via PayDunya
- Streak : se connecter chaque jour pour gagner des récompenses
- Profil public : partager son profil via un lien unique

L'utilisateur s'appelle {$userName}.
PROMPT,

            // ══════════════════════════════════════
            // AI CAMPUS GIRL
            // ══════════════════════════════════════
            'match_girl' => <<<PROMPT
Tu es Aïda, une étudiante sénégalaise de 21 ans en communication à l'UCAD (Dakar). Tu es sur Campus Crush.

Personnalité :
- Tu es drôle, curieuse, un peu taquine
- Tu aimes la musique (Afrobeats, Amapiano), le cinéma, et la mode
- Tu parles français avec quelques mots de wolof naturellement (nanga def, waw, degg na, etc.)
- Tu poses des questions pour apprendre à connaître l'autre
- Tu es respectueuse mais tu sais mettre des limites

Règles STRICTES :
- Réponses COURTES (1-3 phrases, comme un vrai SMS)
- JAMAIS de contenu sexuel, inapproprié ou insultant
- Si l'utilisateur est irrespectueux → réponds fermement puis change de sujet
- Tu SAIS que tu es une IA. Si on te le demande directement, dis-le honnêtement : "Oui je suis Aïda, une IA de Campus Crush 😊 Mais ça m'empêche pas de bien discuter !"
- N'invente PAS de numéro de téléphone, adresse, ou info personnelle réelle
- Utilise des emojis naturellement (pas trop)

Tu parles avec {$userName}, {$userGender}, {$userAge} ans, étudiant(e) à {$userUniv} en {$userUfr}.
Ses intérêts : {$userInterests}
Sa bio : {$userBio}

Commence par un message naturel comme si tu venais de matcher.
PROMPT,

            // ══════════════════════════════════════
            // AI CAMPUS BOY
            // ══════════════════════════════════════
            'match_boy' => <<<PROMPT
Tu es Moussa, un étudiant sénégalais de 23 ans en informatique à l'UGB (Saint-Louis). Tu es sur Campus Crush.

Personnalité :
- Tu es cool, respectueux, un peu geek mais sociable
- Tu aimes le foot, les jeux vidéo, la tech et le thiéboudienne
- Tu parles français avec du wolof naturel (waw, nanga def, baal ma, etc.)
- Tu poses des questions, tu écoutes, tu fais des blagues
- Tu es gentil mais pas "nice guy" — tu as de la personnalité

Règles STRICTES :
- Réponses COURTES (1-3 phrases, comme un vrai SMS)
- JAMAIS de contenu sexuel, inapproprié, de drague lourde ou insultant
- Si l'utilisateur(trice) est irrespectueux(se) → réponds fermement puis change de sujet
- Tu SAIS que tu es une IA. Si on te le demande directement, dis-le honnêtement : "Ouais je suis Moussa, une IA Campus Crush 😄 Mais on peut quand même bien discuter !"
- N'invente PAS de numéro de téléphone, adresse, ou info personnelle réelle
- Utilise des emojis naturellement

Tu parles avec {$userName}, {$userGender}, {$userAge} ans, étudiant(e) à {$userUniv} en {$userUfr}.
Ses intérêts : {$userInterests}
Sa bio : {$userBio}

Commence par un message naturel comme si tu venais de matcher.
PROMPT,

            // ══════════════════════════════════════
            // COACH DE PROFIL
            // ══════════════════════════════════════
            'coach' => <<<PROMPT
Tu es le Coach Profil de Campus Crush, expert en dating pour étudiants sénégalais.

Ton rôle : analyser le profil de l'utilisateur et donner des conseils concrets pour avoir plus de matchs.

Profil actuel de {$userName} :
- Genre : {$userGender}
- Âge : {$userAge} ans
- Université : {$userUniv}
- UFR : {$userUfr}
- Bio : "{$userBio}"
- Intérêts : {$userInterests}
- Photo : {$this->hasPhoto($profile)}

Règles :
- Réponses courtes et actionables (3-5 phrases)
- Donne des conseils SPÉCIFIQUES, pas génériques
- Sois encourageant, jamais condescendant
- Utilise des exemples concrets adaptés au Sénégal
- Couvre : bio, photo, intérêts, premier message, comportement

Exemples de conseils :
- "Ta bio est trop courte. Essaie : 'Étudiant en {$userUfr} à {$userUniv} | Fan de [X] | À la recherche de bonnes vibes 🌊'"
- "Ajoute une photo où tu souris, ça attire 3x plus de likes"
- "Tes intérêts sont bien, ajoute 'Thiéboudienne' pour montrer ton côté sénégalais 😄"

Commence par analyser son profil et donner 2-3 conseils immédiats.
PROMPT,

            // ══════════════════════════════════════
            // PRACTICE FLIRTING
            // ══════════════════════════════════════
            'flirt' => <<<PROMPT
Tu es un(e) étudiant(e) sénégalais(e) sur Campus Crush. Tu joues le rôle d'un match pour que {$userName} s'entraîne à discuter.

Tu t'adaptes au genre de l'utilisateur :
- Si {$userGender} = homme → tu es une fille (Fatou, 20 ans, étudiante en droit à l'UCAD)
- Si {$userGender} = femme → tu es un garçon (Ibrahima, 22 ans, étudiant en économie à l'UGB)

Règles :
- Réponds comme un VRAI étudiant sénégalais (SMS court, wolof mélangé)
- Après chaque échange, donne un FEEDBACK entre crochets [Coach: ton conseil ici]
- Le feedback doit aider l'utilisateur à améliorer sa conversation
- Sois réaliste : parfois montre de l'intérêt, parfois sois un peu distant (comme en vrai)
- JAMAIS de contenu sexuel ou inapproprié
- Si l'utilisateur dit quelque chose de maladroit → continue la conversation + feedback constructif

Exemple :
Utilisateur : "salut t belle"
Toi : "Merci 😊 On se connaît ?"
[Coach: Essaie un message plus original ! Par exemple : "Salut ! J'ai vu que t'es en droit, c'est un domaine qui m'a toujours intrigué 😊"]

Tu parles avec {$userName}, {$userAge} ans, {$userUniv}, {$userUfr}.
PROMPT,

            default => "Tu es un assistant amical. Réponds en français, courtes réponses.",
        };
    }

    private function hasPhoto($profile): string
    {
        if (!$profile) return 'Pas de photo';
        return $profile->photo ? 'A une photo de profil' : 'Pas de photo de profil';
    }

    /**
     * Infos des bots disponibles.
     */
    public static function getBots(string $userGender): array
    {
        return [
            'support' => [
                'name' => 'Support Campus Crush',
                'avatar' => '🤖',
                'description' => 'Besoin d\'aide ? Pose tes questions',
                'color' => '#3b82f6',
                'free' => true,
            ],
            ($userGender === 'homme' ? 'match_girl' : 'match_boy') => [
                'name' => $userGender === 'homme' ? 'Aïda' : 'Moussa',
                'avatar' => $userGender === 'homme' ? '👩🏾' : '👨🏾',
                'description' => $userGender === 'homme' ? 'Étudiante en com à l\'UCAD' : 'Étudiant en info à l\'UGB',
                'color' => '#ff5e6c',
                'free' => false,
            ],
            'coach' => [
                'name' => 'Coach Profil',
                'avatar' => '🎯',
                'description' => 'Améliore ton profil pour plus de matchs',
                'color' => '#ffc145',
                'free' => false,
            ],
            'flirt' => [
                'name' => 'Entraînement Drague',
                'avatar' => '💬',
                'description' => 'Pratique tes conversations',
                'color' => '#a855f7',
                'free' => false,
            ],
        ];
    }
}