{{-- resources/views/layouts/guest.blade.php : layout of the pages for visitors (login, forgot / reset password) --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Collex')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="flex min-h-screen items-center justify-center bg-base px-4 py-10">

    {{-- soft green glow behind the card --}}
    <div class="pointer-events-none fixed inset-0 -z-10 bg-[radial-gradient(circle_at_50%_0%,rgba(0,255,102,0.10),transparent_55%)]"></div>

    <main class="w-full max-w-md">

        {{-- brand --}}
        <div class="mb-8 flex flex-col items-center text-center">
            <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-[#00ff66] to-[#00b84d] text-black shadow-lg shadow-[#00ff66]/20">
                <i class="fa-solid fa-chart-line text-2xl"></i>
            </span>
            <h1 class="mt-4 text-2xl font-bold tracking-wide text-fg">كولكتس</h1>
            <p class="mt-1 text-xs text-muted">نظام إدارة التحصيل</p>
        </div>

        <div class="card p-8 shadow-2xl shadow-black/40">
            <x-flash />
            @yield('content')
        </div>

        <p class="mt-6 text-center text-[11px] text-dim">Collex System {{ now()->year }}</p>
    </main>

</body>
</html>