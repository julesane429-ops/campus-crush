<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0c0a1a">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Campus Crush - Installer l'app</title>
    <link rel="manifest" href="/manifest.json">
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Sora', sans-serif; box-sizing: border-box; }
        body { background: linear-gradient(160deg, #0c0a1a 0%, #1a1145 40%, #0f1a3a 100%); }
        .cc-gradient-text { background: linear-gradient(135deg, #ff5e6c, #ff8a5c, #ffc145); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .cc-surface { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); backdrop-filter: blur(24px); }
        .cc-surface-raised { background: linear-gradient(135deg, rgba(255,255,255,0.06), rgba(255,255,255,0.02)); border: 1px solid rgba(255,255,255,0.08); backdrop-filter: blur(30px); }
        @keyframes fadeUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:none; } }
        .fade-up { animation: fadeUp 0.5s ease both; }
        .tab-btn.active { background: linear-gradient(135deg, #ff5e6c, #ff8a5c); color: white; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .step-num {
            width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: 700; flex-shrink: 0;
            background: linear-gradient(135deg, rgba(255,94,108,0.15), rgba(255,138,92,0.1));
            border: 1px solid rgba(255,94,108,0.2); color: #ff5e6c;
        }
        .orb { position: fixed; border-radius: 50%; filter: blur(80px); pointer-events: none; opacity: 0.12; }
    </style>
</head>
<body class="min-h-screen text-white">
    <div class="orb" style="width:300px;height:300px;background:#ff5e6c;top:-100px;right:-100px;"></div>
    <div class="orb" style="width:250px;height:250px;background:#a855f7;bottom:-80px;left:-80px;"></div>

    <div class="relative z-10 w-full max-w-md mx-auto px-5 py-8">

        {{-- Header --}}
        <div class="flex items-center gap-4 mb-8 fade-up">
            <a href="{{ url()->previous() }}" class="p-2 rounded-xl cc-surface hover:bg-white/10 transition">
                <svg class="w-5 h-5 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h1 class="text-xl font-bold">Installer l'app</h1>
        </div>

        {{-- App preview --}}
        <div class="cc-surface-raised rounded-3xl p-6 mb-6 text-center fade-up" style="animation-delay:0.1s">
            <img src="/images/icons/icon-192x192.png" class="w-20 h-20 rounded-2xl mx-auto mb-4 shadow-lg" alt="Campus Crush">
            <h2 class="text-lg font-bold cc-gradient-text mb-1">Campus Crush</h2>
            <p class="text-white/30 text-xs">Installe l'app pour un accès rapide depuis ton écran d'accueil</p>
        </div>

        {{-- Device tabs --}}
        <div class="flex gap-2 mb-6 fade-up" style="animation-delay:0.2s">
            <button class="tab-btn active flex-1 py-3 rounded-xl text-xs font-semibold transition" data-tab="android">
                🤖 Android
            </button>
            <button class="tab-btn flex-1 py-3 rounded-xl text-xs font-semibold text-white/40 bg-white/5 transition" data-tab="iphone">
                🍎 iPhone
            </button>
            <button class="tab-btn flex-1 py-3 rounded-xl text-xs font-semibold text-white/40 bg-white/5 transition" data-tab="desktop">
                💻 PC
            </button>
        </div>

        {{-- ═══════════════════════════════════ --}}
        {{-- ANDROID --}}
        {{-- ═══════════════════════════════════ --}}
        <div class="tab-content active fade-up" id="tab-android" style="animation-delay:0.3s">
            <div class="cc-surface rounded-3xl p-6 space-y-6">
                <div class="flex items-center gap-3 mb-2">
                    <span class="text-2xl">🤖</span>
                    <div>
                        <h3 class="font-semibold text-sm">Android (Chrome)</h3>
                        <p class="text-[11px] text-white/30">Fonctionne avec Google Chrome</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="step-num">1</div>
                    <div class="flex-1">
                        <p class="text-sm font-medium mb-1">Ouvre Chrome</p>
                        <p class="text-xs text-white/40">Va sur <span class="text-[#ff5e6c] font-mono text-[11px]">campus-crush-h9df.onrender.com</span></p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="step-num">2</div>
                    <div class="flex-1">
                        <p class="text-sm font-medium mb-1">Appuie sur les 3 points</p>
                        <p class="text-xs text-white/40">En haut à droite de Chrome, clique sur le menu <span class="text-white/60">⋮</span></p>
                        <div class="mt-2 cc-surface rounded-xl p-3 flex items-center gap-3">
                            <span class="text-lg">⋮</span>
                            <span class="text-xs text-white/50">Menu Chrome</span>
                        </div>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="step-num">3</div>
                    <div class="flex-1">
                        <p class="text-sm font-medium mb-1">Clique "Installer l'application"</p>
                        <p class="text-xs text-white/40">Ou "Ajouter à l'écran d'accueil" selon ta version de Chrome</p>
                        <div class="mt-2 cc-surface rounded-xl p-3">
                            <div class="flex items-center gap-3 text-xs text-white/60">
                                <span>📲</span> Installer l'application
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="step-num">4</div>
                    <div class="flex-1">
                        <p class="text-sm font-medium mb-1">Confirme l'installation</p>
                        <p class="text-xs text-white/40">Appuie sur "Installer" dans la popup. L'icône Campus Crush apparaîtra sur ton écran d'accueil !</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="step-num">✓</div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-green-400 mb-1">C'est fait !</p>
                        <p class="text-xs text-white/40">Ouvre Campus Crush directement depuis ton écran d'accueil comme une vraie app 🎉</p>
                    </div>
                </div>
            </div>

            {{-- Quick install button for Android --}}
            <div id="android-install-btn" class="mt-4 hidden">
                <button onclick="installPWA()" class="w-full py-4 rounded-2xl font-semibold text-white text-sm transition hover:-translate-y-0.5 active:scale-[0.98]"
                        style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c); box-shadow: 0 8px 30px rgba(255,94,108,0.3);">
                    📲 Installer maintenant
                </button>
            </div>
        </div>

        {{-- ═══════════════════════════════════ --}}
        {{-- IPHONE --}}
        {{-- ═══════════════════════════════════ --}}
        <div class="tab-content" id="tab-iphone">
            <div class="cc-surface rounded-3xl p-6 space-y-6">
                <div class="flex items-center gap-3 mb-2">
                    <span class="text-2xl">🍎</span>
                    <div>
                        <h3 class="font-semibold text-sm">iPhone / iPad (Safari)</h3>
                        <p class="text-[11px] text-white/30">Fonctionne uniquement avec Safari</p>
                    </div>
                </div>

                <div class="px-3 py-2 rounded-xl bg-yellow-500/10 border border-yellow-500/20">
                    <p class="text-[11px] text-yellow-400">⚠️ Important : utilise Safari, pas Chrome ni un autre navigateur</p>
                </div>

                <div class="flex gap-4">
                    <div class="step-num">1</div>
                    <div class="flex-1">
                        <p class="text-sm font-medium mb-1">Ouvre Safari</p>
                        <p class="text-xs text-white/40">Va sur <span class="text-[#ff5e6c] font-mono text-[11px]">campus-crush-h9df.onrender.com</span></p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="step-num">2</div>
                    <div class="flex-1">
                        <p class="text-sm font-medium mb-1">Appuie sur le bouton Partager</p>
                        <p class="text-xs text-white/40">L'icône carré avec une flèche vers le haut, en bas de l'écran</p>
                        <div class="mt-2 cc-surface rounded-xl p-3 flex items-center justify-center">
                            <svg class="w-7 h-7 text-[#3b82f6]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 00-2.25 2.25v9a2.25 2.25 0 002.25 2.25h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25H15m0-3l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="step-num">3</div>
                    <div class="flex-1">
                        <p class="text-sm font-medium mb-1">Fais défiler et appuie sur "Sur l'écran d'accueil"</p>
                        <p class="text-xs text-white/40">Scroll vers le bas dans le menu de partage</p>
                        <div class="mt-2 cc-surface rounded-xl p-3">
                            <div class="flex items-center gap-3 text-xs text-white/60">
                                <span class="text-lg">➕</span> Sur l'écran d'accueil
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="step-num">4</div>
                    <div class="flex-1">
                        <p class="text-sm font-medium mb-1">Appuie "Ajouter"</p>
                        <p class="text-xs text-white/40">En haut à droite. Tu peux aussi renommer l'app si tu veux.</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="step-num">✓</div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-green-400 mb-1">C'est fait !</p>
                        <p class="text-xs text-white/40">Campus Crush est maintenant sur ton écran d'accueil 🎉</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════ --}}
        {{-- DESKTOP --}}
        {{-- ═══════════════════════════════════ --}}
        <div class="tab-content" id="tab-desktop">
            <div class="cc-surface rounded-3xl p-6 space-y-6">
                <div class="flex items-center gap-3 mb-2">
                    <span class="text-2xl">💻</span>
                    <div>
                        <h3 class="font-semibold text-sm">Ordinateur (Chrome / Edge)</h3>
                        <p class="text-[11px] text-white/30">Fonctionne avec Chrome, Edge ou Brave</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="step-num">1</div>
                    <div class="flex-1">
                        <p class="text-sm font-medium mb-1">Ouvre Chrome ou Edge</p>
                        <p class="text-xs text-white/40">Va sur <span class="text-[#ff5e6c] font-mono text-[11px]">campus-crush-h9df.onrender.com</span></p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="step-num">2</div>
                    <div class="flex-1">
                        <p class="text-sm font-medium mb-1">Clique sur l'icône d'installation</p>
                        <p class="text-xs text-white/40">Dans la barre d'adresse, à droite, tu verras une icône de téléchargement ou un "+" dans un cercle</p>
                        <div class="mt-2 cc-surface rounded-xl p-3 flex items-center gap-3">
                            <div class="flex items-center gap-1 text-white/40 text-xs bg-white/5 rounded-lg px-3 py-2 flex-1">
                                <span class="text-[10px]">🔒</span>
                                <span class="truncate">campus-crush-h9df.onrender.com</span>
                            </div>
                            <div class="w-8 h-8 rounded-lg bg-[#ff5e6c]/10 border border-[#ff5e6c]/20 flex items-center justify-center">
                                <span class="text-sm">📲</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="step-num">3</div>
                    <div class="flex-1">
                        <p class="text-sm font-medium mb-1">Ou utilise le menu</p>
                        <p class="text-xs text-white/40">Clique sur <span class="text-white/60">⋮</span> → "Installer Campus Crush" ou "Créer un raccourci"</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="step-num">4</div>
                    <div class="flex-1">
                        <p class="text-sm font-medium mb-1">Clique "Installer"</p>
                        <p class="text-xs text-white/40">L'app s'ouvrira dans sa propre fenêtre, sans barre d'adresse.</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="step-num">✓</div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-green-400 mb-1">C'est fait !</p>
                        <p class="text-xs text-white/40">Campus Crush apparaît dans tes applications comme une vraie app 🎉</p>
                    </div>
                </div>
            </div>

            {{-- Quick install button for Desktop --}}
            <div id="desktop-install-btn" class="mt-4 hidden">
                <button onclick="installPWA()" class="w-full py-4 rounded-2xl font-semibold text-white text-sm transition hover:-translate-y-0.5 active:scale-[0.98]"
                        style="background: linear-gradient(135deg, #ff5e6c, #ff8a5c); box-shadow: 0 8px 30px rgba(255,94,108,0.3);">
                    📲 Installer maintenant
                </button>
            </div>
        </div>

        {{-- Auto-detect device --}}
        <div class="mt-6 text-center fade-up" style="animation-delay:0.4s">
            <p class="text-[11px] text-white/20" id="device-info"></p>
        </div>

        {{-- Back to app --}}
        <div class="mt-6 text-center fade-up" style="animation-delay:0.5s">
            <a href="/" class="text-xs text-white/30 hover:text-white/60 transition">← Retour à Campus Crush</a>
        </div>
    </div>

    <script>
    // Tab switching
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('active');
                b.classList.add('text-white/40', 'bg-white/5');
            });
            btn.classList.add('active');
            btn.classList.remove('text-white/40', 'bg-white/5');

            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
        });
    });

    // Auto-detect device and switch to correct tab
    (function() {
        const ua = navigator.userAgent;
        let device = 'desktop';
        let info = 'Ordinateur détecté';

        if (/iPhone|iPad|iPod/i.test(ua)) {
            device = 'iphone';
            info = 'iPhone/iPad détecté — utilise Safari';
        } else if (/Android/i.test(ua)) {
            device = 'android';
            info = 'Android détecté — utilise Chrome';
        }

        // Auto-switch tab
        document.querySelector(`[data-tab="${device}"]`)?.click();
        document.getElementById('device-info').textContent = info;
    })();

    // PWA install prompt
    let deferredPrompt;
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        // Show install buttons
        document.getElementById('android-install-btn')?.classList.remove('hidden');
        document.getElementById('desktop-install-btn')?.classList.remove('hidden');
    });

    function installPWA() {
        if (!deferredPrompt) {
            alert('L\'installation n\'est pas disponible pour le moment. Suis les étapes manuelles ci-dessus.');
            return;
        }
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then((choice) => {
            if (choice.outcome === 'accepted') {
                document.getElementById('android-install-btn')?.classList.add('hidden');
                document.getElementById('desktop-install-btn')?.classList.add('hidden');
            }
            deferredPrompt = null;
        });
    }
    </script>
</body>
<<<<<<< HEAD
</html>
=======
</html>
>>>>>>> c807687e2d170d7f002724b84265aad7f5af78ba
