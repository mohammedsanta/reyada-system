<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', 'Collex')
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    >

    <style>

        /* =========================================================
         * COLLEX GUEST BACKGROUND
         * ========================================================= */

        .guest-page {
            position: relative;
            min-height: 100vh;
            overflow: hidden;
            background:
                radial-gradient(
                    circle at 15% 15%,
                    rgba(0, 255, 102, 0.055),
                    transparent 28%
                ),
                radial-gradient(
                    circle at 85% 20%,
                    rgba(0, 136, 255, 0.06),
                    transparent 30%
                ),
                radial-gradient(
                    circle at 50% 100%,
                    rgba(0, 255, 102, 0.035),
                    transparent 35%
                ),
                #0a0c0e;
        }


        /* =========================================================
         * GRID
         * ========================================================= */

        .guest-grid {
            position: absolute;
            inset: 0;

            background-image:
                linear-gradient(
                    rgba(255,255,255,0.018) 1px,
                    transparent 1px
                ),
                linear-gradient(
                    90deg,
                    rgba(255,255,255,0.018) 1px,
                    transparent 1px
                );

            background-size: 42px 42px;

            mask-image:
                radial-gradient(
                    ellipse at center,
                    black 15%,
                    transparent 78%
                );

            -webkit-mask-image:
                radial-gradient(
                    ellipse at center,
                    black 15%,
                    transparent 78%
                );

            pointer-events: none;
        }


        /* =========================================================
         * AMBIENT GLOW
         * ========================================================= */

        .guest-glow {
            position: absolute;
            border-radius: 9999px;
            pointer-events: none;
            filter: blur(90px);
            opacity: 0.35;
            animation: guestFloat 12s ease-in-out infinite;
        }


        .guest-glow-green {
            width: 360px;
            height: 360px;

            top: -120px;
            right: -100px;

            background: rgba(0, 255, 102, 0.08);

            animation-delay: -2s;
        }


        .guest-glow-blue {
            width: 420px;
            height: 420px;

            bottom: -180px;
            left: -120px;

            background: rgba(0, 136, 255, 0.08);

            animation-delay: -6s;
        }


        .guest-glow-center {
            width: 300px;
            height: 300px;

            top: 35%;
            left: 50%;

            transform: translate(-50%, -50%);

            background: rgba(0, 255, 102, 0.025);

            filter: blur(110px);

            animation-delay: -9s;
        }


        @keyframes guestFloat {

            0%,
            100% {
                transform: translate3d(0, 0, 0) scale(1);
            }

            50% {
                transform: translate3d(20px, -18px, 0) scale(1.08);
            }

        }


        /* =========================================================
         * FLOATING ORBS
         * ========================================================= */

        .guest-orb {
            position: absolute;
            border-radius: 9999px;
            pointer-events: none;

            background: rgba(0, 255, 102, 0.7);

            box-shadow:
                0 0 12px rgba(0, 255, 102, 0.3);

            animation:
                guestOrbFloat 8s ease-in-out infinite;
        }


        .guest-orb-1 {
            width: 3px;
            height: 3px;

            top: 18%;
            left: 18%;

            animation-delay: -1s;
        }


        .guest-orb-2 {
            width: 2px;
            height: 2px;

            top: 27%;
            right: 17%;

            background: rgba(0, 136, 255, 0.8);

            box-shadow:
                0 0 10px rgba(0, 136, 255, 0.35);

            animation-delay: -4s;
        }


        .guest-orb-3 {
            width: 3px;
            height: 3px;

            bottom: 24%;
            left: 22%;

            animation-delay: -6s;
        }


        .guest-orb-4 {
            width: 2px;
            height: 2px;

            bottom: 18%;
            right: 24%;

            background: rgba(0, 136, 255, 0.75);

            box-shadow:
                0 0 10px rgba(0, 136, 255, 0.35);

            animation-delay: -3s;
        }


        @keyframes guestOrbFloat {

            0%,
            100% {
                transform: translateY(0);
                opacity: 0.25;
            }

            50% {
                transform: translateY(-18px);
                opacity: 0.8;
            }

        }


        /* =========================================================
         * VIGNETTE
         * ========================================================= */

        .guest-vignette {
            position: absolute;
            inset: 0;

            background:
                radial-gradient(
                    ellipse at center,
                    transparent 30%,
                    rgba(0, 0, 0, 0.22) 75%,
                    rgba(0, 0, 0, 0.5) 100%
                );

            pointer-events: none;
        }


        /* =========================================================
         * CONTENT LAYER
         * ========================================================= */

        .guest-content {
            position: relative;
            z-index: 10;
        }


        /* =========================================================
         * REDUCED MOTION
         * ========================================================= */

        @media (prefers-reduced-motion: reduce) {

            .guest-glow,
            .guest-orb {
                animation: none;
            }

        }


    </style>

</head>


<body class="font-[Cairo] text-fg antialiased">

    <div class="guest-page">

        {{-- Background grid --}}
        <div
            class="guest-grid"
            aria-hidden="true"
        ></div>


        {{-- Ambient lights --}}
        <div
            class="guest-glow guest-glow-green"
            aria-hidden="true"
        ></div>

        <div
            class="guest-glow guest-glow-blue"
            aria-hidden="true"
        ></div>

        <div
            class="guest-glow guest-glow-center"
            aria-hidden="true"
        ></div>


        {{-- Floating particles --}}
        <div
            class="guest-orb guest-orb-1"
            aria-hidden="true"
        ></div>

        <div
            class="guest-orb guest-orb-2"
            aria-hidden="true"
        ></div>

        <div
            class="guest-orb guest-orb-3"
            aria-hidden="true"
        ></div>

        <div
            class="guest-orb guest-orb-4"
            aria-hidden="true"
        ></div>


        {{-- Dark edge vignette --}}
        <div
            class="guest-vignette"
            aria-hidden="true"
        ></div>


        {{-- Existing guest content --}}
        <main class="guest-content min-h-screen">

            <div class="flex min-h-screen items-center justify-center px-4 py-8">

                <div class="w-full max-w-md">

                    @yield('content')

                </div>

            </div>

        </main>

    </div>


    @stack('scripts')

</body>

</html>