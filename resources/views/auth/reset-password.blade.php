{{-- resources/views/auth/reset-password.blade.php --}}
@extends('layouts.guest')

@section('title', 'كلمة مرور جديدة - Collex')

@section('content')

    <h2 class="mb-1 text-lg font-bold text-fg">كلمة مرور جديدة</h2>
    <p class="mb-6 text-xs text-muted">اختر كلمة مرور قوية (8 أحرف على الأقل).</p>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <x-form-field name="email" label="البريد الإلكتروني" type="email" dir="ltr" :value="$email" required readonly />
        <x-form-field name="password" label="كلمة المرور الجديدة" type="password" dir="ltr" autocomplete="new-password" autofocus required />
        <x-form-field name="password_confirmation" label="تأكيد كلمة المرور" type="password" dir="ltr" autocomplete="new-password" required />

        <button type="submit" class="btn btn-primary w-full">
            <i class="fa-solid fa-key text-xs"></i> حفظ كلمة المرور
        </button>
    </form>

@endsection