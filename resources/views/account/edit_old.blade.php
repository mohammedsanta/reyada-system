{{-- resources/views/account/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'حسابي')

@section('content')

    <x-page-header
        title="حسابي"
        subtitle="بياناتك الشخصية وكلمة المرور"
        icon="fa-user-gear"
    />

    <x-flash />

    {{-- Identity --}}
    <div class="card mb-6 flex flex-wrap items-center gap-5">

        <x-avatar
            name="أحمد محمد"
            color="brand"
            size="h-16 w-16 text-base"
        />

        <div class="flex-1">
            <h2 class="text-xl font-bold text-fg">
                أحمد محمد
            </h2>

            <div class="mt-2 flex flex-wrap items-center gap-2">
                <span class="badge badge-outline-info">
                    مدير النظام
                </span>

                <span class="badge badge-neutral font-mono">
                    USR-001
                </span>
            </div>
        </div>

        <div class="text-left text-[11px] text-dim">
            <p>
                آخر دخول:
                <span class="text-muted">
                    03/10/2026 08:30 PM
                </span>
            </p>

            <p class="mt-1">
                عنوان IP:
                <span class="text-muted" dir="ltr">
                    192.168.1.100
                </span>
            </p>
        </div>
    </div>


    <section class="grid grid-cols-1 gap-4 xl:grid-cols-2">

        {{-- Personal data --}}
        <x-section-card
            title="بياناتي"
            icon="fa-id-card"
            color="info"
        >

            <div class="space-y-4">

                <x-form-field
                    name="name"
                    label="الاسم الكامل"
                    value="أحمد محمد"
                    required
                />

                <x-form-field
                    name="phone"
                    label="رقم الهاتف"
                    value="01012345678"
                    dir="ltr"
                    placeholder="01XXXXXXXXX"
                    required
                />

                <div>
                    <label class="form-label">
                        البريد الإلكتروني
                    </label>

                    <input
                        type="email"
                        value="ahmed@example.com"
                        dir="ltr"
                        class="form-input opacity-60"
                        readonly
                    >

                    <p class="mt-1 text-[11px] text-dim">
                        لتغيير البريد الإلكتروني تواصل مع المدير.
                    </p>
                </div>

                {{-- Static button --}}
                <button
                    type="button"
                    class="btn btn-primary"
                >
                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                    حفظ
                </button>

            </div>

        </x-section-card>


        {{-- Password --}}
        <div id="password" class="scroll-mt-6">

            <x-section-card
                title="تغيير كلمة المرور"
                icon="fa-lock"
                color="warning"
            >

                <div class="space-y-4">

                    <x-form-field
                        name="current_password"
                        label="كلمة المرور الحالية"
                        type="password"
                        dir="ltr"
                        autocomplete="current-password"
                        required
                    />

                    <x-form-field
                        name="password"
                        label="كلمة المرور الجديدة"
                        type="password"
                        dir="ltr"
                        autocomplete="new-password"
                        required
                    />

                    <x-form-field
                        name="password_confirmation"
                        label="تأكيد كلمة المرور الجديدة"
                        type="password"
                        dir="ltr"
                        autocomplete="new-password"
                        required
                    />

                    <p class="text-[11px] leading-5 text-dim">
                        8 أحرف على الأقل، ومختلفة عن كلمة المرور الحالية.
                    </p>

                    {{-- Static button --}}
                    <button
                        type="button"
                        class="btn btn-primary"
                    >
                        <i class="fa-solid fa-key text-xs"></i>
                        تغيير كلمة المرور
                    </button>

                </div>

            </x-section-card>

        </div>

    </section>


    {{-- Logout --}}
    <div class="card mt-6 flex flex-wrap items-center justify-between gap-4 border-danger/20">

        <div>
            <h3 class="text-sm font-bold text-fg">
                تسجيل الخروج
            </h3>

            <p class="mt-1 text-xs text-muted">
                أنهِ جلستك الحالية على هذا الجهاز.
            </p>
        </div>

        {{-- Static button --}}
        <button
            type="button"
            class="btn btn-danger"
        >
            <i class="fa-solid fa-right-from-bracket text-xs"></i>
            تسجيل الخروج
        </button>

    </div>

@endsection