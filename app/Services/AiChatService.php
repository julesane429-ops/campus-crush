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
        $this->apiKey   = config('services.claude.key', env('ANTHROPIC_API_KEY', ''));
        $this->model    = config('services.claude.model', 'claude-haiku-4-5-20251001'); // Modèle économique
        $this->maxTokens = 300; // Réponses courtes pour contrôler les coûts
    }

    /**
     * Envoyer un message et obtenir la réponse IA.
     */
    public function chat(AiChatSession $session, string $userMessage): string
    {
        if ($this->fakeMode()) {
            Log::info('AI chat fake mode enabled', ['bot_type' => $session->bot_type]);
            return $this->fakeReply($session, $userMessage);
        }

        if (empty($this->apiKey)) {
            Log::error('ANTHROPIC_API_KEY is not set. Add it to Render environment variables.');
            return 'Le service IA n\'est pas configuré. Contacte le support Campus Crush. 🔧';
        }

        $user    = $session->user;
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

        // Construire les messages pour l'API Claude
        // Note : Claude sépare le system prompt du tableau messages
        $messages = [];

        foreach ($history as $msg) {
            $messages[] = ['role' => $msg->role, 'content' => $msg->content];
        }

        $messages[] = ['role' => 'user', 'content' => $userMessage];

        try {
            $client = Http::withOptions(['proxy' => ''])->withHeaders([
                'x-api-key'         => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'Content-Type'      => 'application/json',
            ])->timeout(30);

            if (app()->environment('local') && config('services.http.verify_ssl') === false) {
                $client = $client->withoutVerifying();
            }

            $response = $client->post('https://api.anthropic.com/v1/messages', [
                'model'       => $this->model,
                'system'      => $systemPrompt,   // <-- séparé des messages chez Claude
                'messages'    => $messages,
                'max_tokens'  => $this->maxTokens,
                'temperature' => $this->getTemperature($session->bot_type),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                // Réponse Claude : content[0].text
                return $data['content'][0]['text'] ?? 'Désolé, je n\'ai pas compris. Réessaie 😊';
            }

            if ($response->status() === 401) {
                Log::error('Claude API authentication failed', ['status' => 401]);

                if (app()->environment('local')) {
                    return 'Mode IA reel non configure: la cle Anthropic locale est invalide. Active AI_CHAT_FAKE=true ou remplace ANTHROPIC_API_KEY.';
                }
            }

            Log::error('Claude API error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return 'Oups, j\'ai un petit souci technique. Réessaie dans quelques secondes 🙏';

        } catch (\Exception $e) {
            Log::error('Claude exception', ['message' => $e->getMessage()]);
            return 'Connexion perdue. Vérifie ta connexion internet et réessaie 📱';
        }
    }

    private function fakeMode(): bool
    {
        return app()->environment('local')
            && filter_var(config('services.claude.fake', false), FILTER_VALIDATE_BOOL);
    }

    private function fakeReply(AiChatSession $session, string $userMessage): string
    {
        $user = $session->user;
        $profile = $user->profile;
        $university = $profile?->university_name ?? 'ton campus';
        $ufr = $profile?->ufr ? ' en ' . $profile->ufr : '';
        $message = trim($userMessage);

        return match ($session->bot_type) {
            'support' => "Mode test local actif. Je peux repondre sans appeler Anthropic. Pour l'app: inscription, profil, swipe, IA et paiements SoftPay peuvent etre testes depuis ce serveur local.",
            'coach' => "Ton profil a deja une base: {$university}{$ufr}. Pour avoir plus de matchs, ajoute une bio concrete, une photo nette ou tu souris, et 3 centres d'interet faciles a relancer.",
            'match_girl' => "Waw, j'aime bien ton message. Tu etudies a {$university}, c'est comment l'ambiance la-bas ?",
            'match_boy' => "Ah je vois. {$university}, c'est solide. Tu es plutot bibliotheque serieuse ou discussions apres les cours ?",
            'flirt' => "Bonne ouverture. [Coach: garde le message simple, mais ajoute un detail du profil pour montrer que tu as vraiment lu.] Tu peux rebondir sur: \"{$message}\".",
            default => 'Je suis en mode test local et je reponds sans API externe.',
        };
    }

    /**
     * Température selon le type de bot.
     */
    private function getTemperature(string $botType): float
    {
        return match ($botType) {
            'support'                    => 0.3,  // Précis et factuel
            'coach'                      => 0.5,  // Conseils structurés
            'match_girl', 'match_boy'    => 0.8,  // Naturel et varié
            'flirt'                      => 0.9,  // Créatif et fun (max 1.0 chez Claude)
            default                      => 0.7,
        };
    }

    /**
     * Prompt système selon le type de bot.
     */
    private function getSystemPrompt(string $botType, User $user, $profile): string
    {
        $userName      = $user->name;
        $userGender    = $profile?->gender ?? 'inconnu';
        $userAge       = $profile?->age ?? '';
        $userUniv      = $profile?->university_name ?? 'université';
        $userUfr       = $profile?->ufr ?? '';
        $userBio       = $profile?->bio ?? '';
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

            default => "Tu es un assistant amical. Réponds en français, réponses courtes.",
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
                'name'        => 'Support Campus Crush',
                'avatar'      => '🤖',
                'description' => 'Besoin d\'aide ? Pose tes questions',
                'color'       => '#3b82f6',
                'free'        => true,
            ],
            ($userGender === 'homme' ? 'match_girl' : 'match_boy') => [
                'name'        => $userGender === 'homme' ? 'Aïda' : 'Moussa',
                'avatar'      => $userGender === 'homme' ? '👩🏾' : '👨🏾',
                'description' => $userGender === 'homme' ? 'Étudiante en com à l\'UCAD' : 'Étudiant en info à l\'UGB',
                'color'       => '#ff5e6c',
                'free'        => false,
            ],
            'coach' => [
                'name'        => 'Coach Profil',
                'avatar'      => '🎯',
                'description' => 'Améliore ton profil pour plus de matchs',
                'color'       => '#ffc145',
                'free'        => false,
            ],
            'flirt' => [
                'name'        => 'Entraînement Drague',
                'avatar'      => '💬',
                'description' => 'Pratique tes conversations',
                'color'       => '#a855f7',
                'free'        => false,
            ],
        ];
    }
}
