{{-- resources/views/boost/success.blade.php --}}
<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    @include('components.pwa-meta')
    <title>Boost activé · Campus Crush</title>
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Sora', sans-serif;
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: linear-gradient(160deg, #0c0a1a 0%, #1a1145 40%, #0f1a3a 100%);
            min-height: 100vh;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        @keyframes popIn {
            0% {
                opacity: 0;
                transform: scale(0.5)
            }

            70% {
                transform: scale(1.1)
            }

            100% {
                opacity: 1;
                transform: scale(1)
            }
        }

        .pop-in {
            animation: popIn 0.6s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        @keyframes pulse-boost {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(255, 193, 69, 0.5)
            }

            50% {
                box-shadow: 0 0 0 16px rgba(255, 193, 69, 0)
            }
        }

        .boost-pulse {
            animation: pulse-boost 2s ease-in-out infinite;
        }
    </style>
</head>

<body>
    <div class="text-center max-w-sm w-full">
        <div class="text-7xl mb-6 boost-pulse pop-in inline-block">🚀</div>
        <h1 class="text-3xl font-extrabold mb-2" style="background:linear-gradient(135deg,#ffc145,#ff8a5c);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">
            Boost activé !
        </h1>
        <p class="text-white/40 text-sm mb-2">Ton profil est en tête du swipe pendant 24h</p>
        @if($isBoosted && $boostedUntil)
        <p class="text-xs mb-8" style="color:rgba(255,255,255,0.25);">
            Actif jusqu'au {{ $boostedUntil->format('d/m/Y à H:i') }}
        </p>
        @endif
        <a href="{{ route('swipe') }}" class="block w-full py-4 rounded-2xl font-bold text-white"
            style="background:linear-gradient(135deg,#ff5e6c,#ff8a5c); box-shadow:0 8px 30px rgba(255,94,108,0.3);">
            Swiper maintenant →
        </a>
        <a href="{{ route('profile.show') }}" class="block mt-3 text-sm" style="color:rgba(255,255,255,0.25);">
            Voir mon profil
        </a>
    </div>
</body>

</html>