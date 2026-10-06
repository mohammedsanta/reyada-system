{{-- resources/views/errors/layout.blade.php
     Shared look of every error page. It is intentionally SELF-CONTAINED: no sidebar, no session, no database,
     because an error page must still work when those are exactly what failed.
     Each error file only sets: code, title, message, tone (danger | warning | info) and optionally icon / actions. --}}
@php
    $tone = trim($__env->yieldContent('tone')) ?: 'info';

    // full class names so Tailwind can detect them
    $tones = [
        'danger'  => ['bg-danger/15 text-danger',   'text-danger'],
        'warning' => ['bg-warning/15 text-warning', 'text-warning'],
        'info'    => ['bg-info/15 text-info',       'text-info'],
    ];
    [$iconBox, $codeText] = $tones[$tone] ?? $tones['info'];
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('code') - @yield('title')</title>

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite(['resources/css/app.css'])
</head>

<body class="flex min-h-screen items-center justify-center bg-base px-4 py-10 text-center">

    <div class="pointer-events-none fixed inset-0 -z-10 bg-[radial-gradient(circle_at_50%_0%,rgba(255,255,255,0.05),transparent_55%)]"></div>

    <main class="w-full max-w-lg">

        <span class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl {{ $iconBox }}">
            <i class="fa-solid @yield('icon', 'fa-circle-exclamation') text-3xl"></i>
        </span>

        <p class="mt-6 text-7xl font-bold tracking-widest {{ $codeText }}">@yield('code')</p>
        <h1 class="mt-3 text-2xl font-bold text-fg">@yield('title')</h1>
        <p class="mt-3 text-sm leading-7 text-muted">@yield('message')</p>

        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            @hasSection('actions')
                @yield('actions')
            @else
                <button type="button" onclick="history.back()" class="btn btn-secondary"><i class="fa-solid fa-arrow-right text-xs"></i> رجوع</button>
                <a href="{{ url('/') }}" class="btn btn-primary"><i class="fa-solid fa-house text-xs"></i> الصفحة الرئيسية</a>
            @endif
        </div>

        <p class="mt-10 text-[11px] text-dim">Collex System</p>
    </main>

</body>
</html>