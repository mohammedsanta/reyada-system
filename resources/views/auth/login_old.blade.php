{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.guest')

@section('title', 'تسجيل الدخول - Collex')

@section('content')

    <h2 class="mb-1 text-lg font-bold text-fg">تسجيل الدخول</h2>
    <p class="mb-6 text-xs text-muted">أدخل بياناتك للوصول إلى لوحة التحكم</p>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <x-form-field name="email" label="البريد الإلكتروني" type="email" dir="ltr" autocomplete="username" autofocus required placeholder="name@collex.com" />
        <x-form-field name="password" label="كلمة المرور" type="password" dir="ltr" autocomplete="current-password" required />

        <div class="flex items-center justify-between">
            <label class="flex cursor-pointer items-center gap-2 text-xs text-muted">
                <input type="checkbox" name="remember" value="1" class="accent-brand" @checked(old('remember'))>
                تذكرني
            </label>

            <a href="{{ route('password.request') }}" class="text-xs font-semibold text-info hover:underline">نسيت كلمة المرور؟</a>
        </div>

        <button type="submit" class="btn btn-primary w-full">
            <i class="fa-solid fa-right-to-bracket text-xs"></i> دخول
        </button>
    </form>

@endsection