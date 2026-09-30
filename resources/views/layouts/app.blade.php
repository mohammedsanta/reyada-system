{{-- resources/views/layouts/app.blade.php (UPDATED: Font Awesome + scripts stack) --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Collex')</title>

    {{-- Cairo font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">

    @include('layouts._aside')

    {{-- mr-64 leaves room for the fixed sidebar (w-64) on the right --}}
    <main class="mr-64 min-h-screen overflow-y-auto p-8">
        @yield('content')
    </main>

    {{-- Page-specific scripts (charts, etc.) --}}
    @stack('scripts')
</body>
</html>