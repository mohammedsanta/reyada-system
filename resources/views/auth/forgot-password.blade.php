{{-- resources/views/auth/forgot-password.blade.php --}}
@extends('layouts.guest')

@section('title', 'استعادة كلمة المرور - Collex')

@section('content')

    <h2 class="mb-1 text-lg font-bold text-fg">استعادة كلمة المرور</h2>
    <p class="mb-6 text-xs leading-6 text-muted">اكتب بريدك الإلكتروني وسنرسل لك رابطاً لتعيين كلمة مرور جديدة.</p>

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <x-form-field name="email" label="البريد الإلكتروني" type="email" dir="ltr" autofocus required placeholder="name@collex.com" />

        <button type="submit" class="btn btn-primary w-full">
            <i class="fa-solid fa-paper-plane text-xs"></i> إرسال رابط الاستعادة
        </button>

        <a href="{{ route('login') }}" class="block text-center text-xs font-semibold text-muted transition hover:text-fg">
            <i class="fa-solid fa-arrow-right ml-1 text-[10px]"></i> الرجوع لتسجيل الدخول
        </a>
    </form>

@endsection