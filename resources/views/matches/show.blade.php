<!doctype html>
<html lang="fr" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>C'est un Match !</title>
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <script src="/_sdk/element_sdk.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&amp;family=Nunito:wght@400;600;700;800&amp;display=swap" rel="stylesheet">
    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        .font-pacifico {
            font-family: 'Pacifico', cursive;
        }

        .font-nunito {
            font-family: 'Nunito', sans-serif;
        }

        /* Confetti animation */
        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            opacity: 0;
            animation: confetti-fall 3s ease-in-out infinite;
        }

        @keyframes confetti-fall {
            0% {
                transform: translateY(-100%) rotate(0deg);
                opacity: 1;
            }

            100% {
                transform: translateY(800%) rotate(720deg);
                opacity: 0;
            }
        }

        /* Heart pulse animation */
        @keyframes heart-pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.2);
            }
        }

        .heart-pulse {
            animation: heart-pulse 1.5s ease-in-out infinite;
        }

        /* Photo glow animation */
        @keyframes glow {

            0%,
            100% {
                box-shadow: 0 0 20px rgba(255, 105, 180, 0.6), 0 0 40px rgba(255, 105, 180, 0.4);
            }

            50% {
                box-shadow: 0 0 30px rgba(255, 105, 180, 0.8), 0 0 60px rgba(255, 105, 180, 0.6);
            }
        }

        .photo-glow {
            animation: glow 2s ease-in-out infinite;
        }

        /* Title shimmer effect */
        @keyframes shimmer {
            0% {
                background-position: -200% center;
            }

            100% {
                background-position: 200% center;
            }
        }

        .shimmer-text {
            background: linear-gradient(90deg, #fff 0%, #fff 40%, #ffe4f0 50%, #fff 60%, #fff 100%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            background-clip: text;
            animation: shimmer 3s linear infinite;
        }

        /* Floating hearts */
        @keyframes float-up {
            0% {
                transform: translateY(0) scale(0);
                opacity: 0;
            }

            10% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }

            90% {
                opacity: 1;
            }

            100% {
                transform: translateY(-300px) scale(0.5);
                opacity: 0;
            }
        }

        .floating-heart {
            position: absolute;
            animation: float-up 4s ease-out infinite;
        }

        /* Button hover effects */
        .btn-primary {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(255, 105, 180, 0.4);
        }

        .btn-secondary {
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        /* Profile connection line */
        @keyframes dash {
            to {
                stroke-dashoffset: 0;
            }
        }

        .connection-line {
            stroke-dasharray: 200;
            stroke-dashoffset: 200;
            animation: dash 1.5s ease-out forwards;
        }

        /* Entrance animations */
        @keyframes slide-up {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes scale-in {
            from {
                opacity: 0;
                transform: scale(0.5);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .animate-slide-up {
            animation: slide-up 0.6s ease-out forwards;
        }

        .animate-scale-in {
            animation: scale-in 0.5s ease-out forwards;
        }

        .delay-100 {
            animation-delay: 0.1s;
        }

        .delay-200 {
            animation-delay: 0.2s;
        }

        .delay-300 {
            animation-delay: 0.3s;
        }

        .delay-400 {
            animation-delay: 0.4s;
        }

        .delay-500 {
            animation-delay: 0.5s;
        }

        .delay-600 {
            animation-delay: 0.6s;
        }
    </style>
    <style>
        body {
            box-sizing: border-box;
        }
    </style>
    <script src="/_sdk/data_sdk.js" type="text/javascript"></script>
</head>

<body class="h-full font-nunito">
    <div id="app" class="h-full w-full overflow-hidden relative" style="background: linear-gradient(135deg, #667eea 0%, #f093fb 50%, #ff6b9d 100%);"><!-- Confetti Container -->
        <div id="confetti-container" class="absolute inset-0 pointer-events-none overflow-hidden"></div><!-- Floating Hearts Container -->
        <div id="hearts-container" class="absolute inset-0 pointer-events-none overflow-hidden"></div><!-- Main Content -->
        <div class="h-full flex flex-col items-center justify-center px-6 relative z-10">
            <!-- Match Title -->
            <div class="text-center mb-8 opacity-0 animate-scale-in">
                <h1 id="match-title" class="font-pacifico text-5xl md:text-6xl text-white shimmer-text drop-shadow-lg">C'est un Match !</h1>
            </div>

            <!-- Matches Loop -->
            @foreach($matches as $match)
            <div class="relative flex items-center justify-center mb-8 opacity-0 animate-scale-in delay-200">
                <!-- Heart Icon in Center -->
                <div class="absolute z-20 heart-pulse">
                    <div class="w-16 h-16 rounded-full bg-white flex items-center justify-center shadow-xl">
                        <svg class="w-10 h-10 text-pink-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                        </svg>
                    </div>
                </div>

                <!-- Left Profile Photo (Current User) -->
                <div class="relative -mr-6 z-10">
                    <div class="w-32 h-32 md:w-40 md:h-40 rounded-full photo-glow overflow-hidden border-4 border-white">
                        <img src="{{ Auth::user()->profile?->photo 
                        ? Auth::user()->profile->photo_url 
                        : asset('profiles/default-avatar.png') }}"
                            class="w-full h-full object-cover">
                    </div>
                </div>

                <!-- Right Profile Photo (Other User) -->
                <div class="relative -ml-6 z-0">
                    <div class="w-32 h-32 md:w-40 md:h-40 rounded-full photo-glow overflow-hidden border-4 border-white">
                        <img src="{{ $match['photo'] }}" class="w-full h-full object-cover">
                    </div>
                </div>
            </div>

            <!-- Subtitle Message -->
            <p class="text-white text-lg md:text-xl text-center mb-10 opacity-0 animate-slide-up delay-300 font-semibold drop-shadow">
                Vous vous êtes likés mutuellement avec {{ $match['name'] }}
            </p>

            <!-- Action Buttons -->
            <div class="w-full max-w-xs space-y-4">
                <a href="{{ route('messages.chat', $match['match_id']) }}"
                    class="btn-primary w-full py-4 px-6 bg-white rounded-full text-pink-600 font-bold text-lg shadow-lg flex items-center justify-center gap-3">
                    Envoyer un message
                </a>
                <a href="{{ url('/swipe') }}"
                    class="btn-secondary w-full py-4 px-6 bg-transparent border-2 border-white rounded-full text-white font-bold text-lg flex items-center justify-center gap-3">
                    Continuer à swiper
                </a>
            </div>
            @endforeach
        </div>
    </div>
    <script>
        // Default configuration
        const defaultConfig = {
            match_title: "C'est un Match !",
            match_subtitle: "Vous vous êtes likés mutuellement",
            message_button_text: "Envoyer un message",
            continue_button_text: "Continuer à swiper",
            background_color: "#667eea",
            accent_color: "#ff6b9d",
            text_color: "#ffffff",
            button_color: "#ffffff",
            button_text_color: "#ec4899",
            font_family: "Nunito",
            font_size: 16
        };

        // Create confetti
        function createConfetti() {
            const container = document.getElementById('confetti-container');
            const colors = ['#ff6b9d', '#ffd93d', '#6bcb77', '#4d96ff', '#ff6b6b', '#c9b1ff', '#ffa94d'];
            const shapes = ['square', 'circle'];

            for (let i = 0; i < 50; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + '%';
                confetti.style.animationDelay = Math.random() * 3 + 's';
                confetti.style.animationDuration = (Math.random() * 2 + 2) + 's';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.borderRadius = shapes[Math.floor(Math.random() * shapes.length)] === 'circle' ? '50%' : '2px';
                confetti.style.width = (Math.random() * 8 + 6) + 'px';
                confetti.style.height = (Math.random() * 8 + 6) + 'px';
                container.appendChild(confetti);
            }
        }

        // Create floating hearts
        function createFloatingHearts() {
            const container = document.getElementById('hearts-container');
            const heartColors = ['#ff6b9d', '#ff85a8', '#ffa0b8', '#ffb6c1'];

            for (let i = 0; i < 12; i++) {
                const heart = document.createElement('div');
                heart.className = 'floating-heart';
                heart.innerHTML = `<svg width="24" height="24" viewBox="0 0 24 24" fill="${heartColors[Math.floor(Math.random() * heartColors.length)]}">
          <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
        </svg>`;
                heart.style.left = (Math.random() * 80 + 10) + '%';
                heart.style.bottom = '10%';
                heart.style.animationDelay = Math.random() * 4 + 's';
                heart.style.opacity = Math.random() * 0.5 + 0.5;
                container.appendChild(heart);
            }
        }

        // Update UI based on config
        async function onConfigChange(config) {
            const c = {
                ...defaultConfig,
                ...config
            };

            // Update text content
            document.getElementById('match-title').textContent = c.match_title;
            document.getElementById('match-subtitle').textContent = c.match_subtitle;
            document.getElementById('message-btn-text').textContent = c.message_button_text;
            document.getElementById('continue-btn-text').textContent = c.continue_button_text;

            // Update colors
            document.getElementById('app').style.background = `linear-gradient(135deg, ${c.background_color} 0%, #f093fb 50%, ${c.accent_color} 100%)`;

            // Update text color
            document.getElementById('match-title').style.color = c.text_color;
            document.getElementById('match-subtitle').style.color = c.text_color;

            // Update button colors
            const messageBtn = document.getElementById('message-btn');
            messageBtn.style.backgroundColor = c.button_color;
            messageBtn.style.color = c.button_text_color;

            const continueBtn = document.getElementById('continue-btn');
            continueBtn.style.borderColor = c.text_color;
            continueBtn.style.color = c.text_color;

            // Update fonts
            const customFont = c.font_family;
            const baseFontStack = 'Nunito, sans-serif';
            document.getElementById('match-subtitle').style.fontFamily = `${customFont}, ${baseFontStack}`;
            document.getElementById('message-btn-text').style.fontFamily = `${customFont}, ${baseFontStack}`;
            document.getElementById('continue-btn-text').style.fontFamily = `${customFont}, ${baseFontStack}`;

            // Update font sizes
            const baseSize = c.font_size;
            document.getElementById('match-subtitle').style.fontSize = `${baseSize * 1.125}px`;
            document.getElementById('message-btn-text').style.fontSize = `${baseSize * 1.125}px`;
            document.getElementById('continue-btn-text').style.fontSize = `${baseSize * 1.125}px`;
        }

        // Map to capabilities for Canva editing
        function mapToCapabilities(config) {
            const c = {
                ...defaultConfig,
                ...config
            };
            return {
                recolorables: [{
                        get: () => c.background_color,
                        set: (value) => {
                            c.background_color = value;
                            if (window.elementSdk) window.elementSdk.setConfig({
                                background_color: value
                            });
                        }
                    },
                    {
                        get: () => c.accent_color,
                        set: (value) => {
                            c.accent_color = value;
                            if (window.elementSdk) window.elementSdk.setConfig({
                                accent_color: value
                            });
                        }
                    },
                    {
                        get: () => c.text_color,
                        set: (value) => {
                            c.text_color = value;
                            if (window.elementSdk) window.elementSdk.setConfig({
                                text_color: value
                            });
                        }
                    },
                    {
                        get: () => c.button_color,
                        set: (value) => {
                            c.button_color = value;
                            if (window.elementSdk) window.elementSdk.setConfig({
                                button_color: value
                            });
                        }
                    },
                    {
                        get: () => c.button_text_color,
                        set: (value) => {
                            c.button_text_color = value;
                            if (window.elementSdk) window.elementSdk.setConfig({
                                button_text_color: value
                            });
                        }
                    }
                ],
                borderables: [],
                fontEditable: {
                    get: () => c.font_family,
                    set: (value) => {
                        c.font_family = value;
                        if (window.elementSdk) window.elementSdk.setConfig({
                            font_family: value
                        });
                    }
                },
                fontSizeable: {
                    get: () => c.font_size,
                    set: (value) => {
                        c.font_size = value;
                        if (window.elementSdk) window.elementSdk.setConfig({
                            font_size: value
                        });
                    }
                }
            };
        }

        // Map to edit panel values
        function mapToEditPanelValues(config) {
            const c = {
                ...defaultConfig,
                ...config
            };
            return new Map([
                ["match_title", c.match_title],
                ["match_subtitle", c.match_subtitle],
                ["message_button_text", c.message_button_text],
                ["continue_button_text", c.continue_button_text]
            ]);
        }

        // Initialize SDK
        if (window.elementSdk) {
            window.elementSdk.init({
                defaultConfig,
                onConfigChange,
                mapToCapabilities,
                mapToEditPanelValues
            });
        }

        // Initialize animations
        createConfetti();
        createFloatingHearts();

        // Button interactions
        document.getElementById('message-btn').addEventListener('click', function() {
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });

        document.getElementById('continue-btn')?.addEventListener('click', function() {
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });


        // Initial render
        onConfigChange(defaultConfig);
    </script>
    <script>
        (function() {
            function c() {
                var b = a.contentDocument || a.contentWindow.document;
                if (b) {
                    var d = b.createElement('script');
                    d.innerHTML = "window.__CF$cv$params={r:'9daa900d776cf81a',t:'MTc3MzIzMjE0NS4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";
                    b.getElementsByTagName('head')[0].appendChild(d)
                }
            }
            if (document.body) {
                var a = document.createElement('iframe');
                a.height = 1;
                a.width = 1;
                a.style.position = 'absolute';
                a.style.top = 0;
                a.style.left = 0;
                a.style.border = 'none';
                a.style.visibility = 'hidden';
                document.body.appendChild(a);
                if ('loading' !== document.readyState) c();
                else if (window.addEventListener) document.addEventListener('DOMContentLoaded', c);
                else {
                    var e = document.onreadystatechange || function() {};
                    document.onreadystatechange = function(b) {
                        e(b);
                        'loading' !== document.readyState && (document.onreadystatechange = e, c())
                    }
                }
            }
        })();
    </script>
</body>

</html>