{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.guest')

@section('title', 'تسجيل الدخول - Collex')

@section('content')

    {{-- Login heading --}}
    <div class="mb-6">

        <div class="mb-3 flex items-center gap-2">

            <div class="flex h-9 w-9 items-center justify-center rounded-lg border border-info/20 bg-info/5 text-info">
                <i class="fa-solid fa-shield-halved text-sm"></i>
            </div>

            <span class="text-[10px] font-semibold text-info">
                وصول آمن
            </span>

        </div>

        <h2 class="mb-1 text-lg font-bold text-fg">
            تسجيل الدخول
        </h2>

        <p class="text-xs text-muted">
            أدخل بياناتك للوصول إلى لوحة التحكم
        </p>

    </div>


    {{-- Session / validation messages --}}
    <x-flash />


    {{-- General validation error summary --}}
    @if ($errors->any())

        <div
            class="mb-4 rounded-xl border border-danger/20 bg-danger/5 p-3"
            role="alert"
        >

            <div class="flex items-start gap-2">

                <i class="fa-solid fa-circle-exclamation mt-0.5 text-danger text-xs"></i>

                <div class="min-w-0">

                    <p class="text-xs font-bold text-fg">
                        تعذر تسجيل الدخول
                    </p>

                    <ul class="mt-1 space-y-1 text-[10px] leading-5 text-muted">

                        @foreach ($errors->all() as $error)
                            <li>
                                {{ $error }}
                            </li>
                        @endforeach

                    </ul>

                </div>

            </div>

        </div>

    @endif


    {{-- Login form --}}
    <form
        id="loginForm"
        method="POST"
        action="{{ route('login') }}"
        class="space-y-4"
        autocomplete="on"
    >
        @csrf


        {{-- Email --}}
        <div>

            <x-form-field
                name="email"
                label="البريد الإلكتروني"
                type="email"
                dir="ltr"
                autocomplete="username"
                autofocus
                required
                placeholder="name@collex.com"
            />

            <p
                id="emailHint"
                class="mt-1 hidden text-[10px] text-danger"
            ></p>

        </div>


        {{-- Password --}}
        <div>

            <div class="relative">

                <x-form-field
                    name="password"
                    label="كلمة المرور"
                    type="password"
                    dir="ltr"
                    autocomplete="current-password"
                    required
                />

                {{-- Password visibility --}}
                <button
                    type="button"
                    id="togglePassword"
                    class="absolute left-3 top-[34px] z-10 text-dim transition hover:text-fg"
                    title="إظهار كلمة المرور"
                    aria-label="إظهار كلمة المرور"
                >
                    <i class="fa-solid fa-eye text-xs"></i>
                </button>

            </div>

            <p
                id="passwordHint"
                class="mt-1 hidden text-[10px] text-danger"
            ></p>

        </div>


        {{-- Remember / forgot password --}}
        <div class="flex flex-wrap items-center justify-between gap-3">

            <label class="flex cursor-pointer items-center gap-2 text-xs text-muted">

                <input
                    type="checkbox"
                    name="remember"
                    value="1"
                    class="accent-brand"
                    @checked(old('remember'))
                >

                <span>
                    تذكرني
                </span>

            </label>

            <a
                href="{{ route('password.request') }}"
                class="text-xs font-semibold text-info transition hover:text-info/80 hover:underline"
            >
                نسيت كلمة المرور؟
            </a>

        </div>


        {{-- Login button --}}
        <button
            type="submit"
            id="loginButton"
            class="btn btn-primary w-full"
        >
            <i class="fa-solid fa-right-to-bracket text-xs"></i>
            <span id="loginButtonText">
                دخول
            </span>
        </button>


        {{-- Loading state --}}
        <div
            id="loginLoading"
            class="hidden items-center justify-center gap-2 text-[10px] text-muted"
            aria-live="polite"
        >
            <i class="fa-solid fa-spinner fa-spin text-info"></i>
            جاري التحقق من بيانات الدخول...
        </div>

    </form>


    {{-- Security information --}}
    <div class="mt-6 rounded-xl border border-border bg-card p-3">

        <div class="flex items-start gap-3">

            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-success/5 text-success">
                <i class="fa-solid fa-lock text-xs"></i>
            </div>

            <div class="min-w-0">

                <p class="text-[10px] font-bold text-fg">
                    نصائح للحفاظ على أمان حسابك
                </p>

                <ul class="mt-2 space-y-1 text-[10px] leading-5 text-dim">

                    <li class="flex items-start gap-1.5">
                        <i class="fa-solid fa-check mt-1 text-[8px] text-success"></i>
                        <span>لا تشارك كلمة المرور مع أي شخص.</span>
                    </li>

                    <li class="flex items-start gap-1.5">
                        <i class="fa-solid fa-check mt-1 text-[8px] text-success"></i>
                        <span>تأكد من استخدام جهاز موثوق عند تسجيل الدخول.</span>
                    </li>

                    <li class="flex items-start gap-1.5">
                        <i class="fa-solid fa-check mt-1 text-[8px] text-success"></i>
                        <span>سجّل الخروج عند الانتهاء من استخدام النظام.</span>
                    </li>

                </ul>

            </div>

        </div>

    </div>


    {{-- Keyboard shortcut / accessibility hint --}}
    <div class="mt-4 flex items-center justify-center gap-2 text-[9px] text-dim">

        <span>
            اضغط
        </span>

        <kbd class="rounded border border-border bg-card px-1.5 py-0.5 font-mono text-[9px] text-muted">
            Enter
        </kbd>

        <span>
            لتسجيل الدخول
        </span>

    </div>


    {{-- Footer --}}
    <div class="mt-6 border-t border-border pt-4 text-center">

        <p class="text-[10px] text-dim">
            Collex
            <span class="mx-1 text-border">•</span>
            نظام إدارة التحصيل
        </p>

    </div>


    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {

                /* =========================================================
                 * ELEMENTS
                 * ========================================================= */

                const form = document.getElementById('loginForm');

                const emailInput =
                    document.querySelector('input[name="email"]');

                const passwordInput =
                    document.querySelector('input[name="password"]');

                const togglePassword =
                    document.getElementById('togglePassword');

                const loginButton =
                    document.getElementById('loginButton');

                const loginButtonText =
                    document.getElementById('loginButtonText');

                const loginLoading =
                    document.getElementById('loginLoading');

                const emailHint =
                    document.getElementById('emailHint');

                const passwordHint =
                    document.getElementById('passwordHint');


                /* =========================================================
                 * PASSWORD VISIBILITY
                 * ========================================================= */

                if (togglePassword && passwordInput) {

                    togglePassword.addEventListener(
                        'click',
                        function () {

                            const isPassword =
                                passwordInput.type === 'password';

                            passwordInput.type =
                                isPassword
                                    ? 'text'
                                    : 'password';

                            const icon =
                                this.querySelector('i');

                            if (icon) {

                                icon.classList.toggle(
                                    'fa-eye',
                                    !isPassword
                                );

                                icon.classList.toggle(
                                    'fa-eye-slash',
                                    isPassword
                                );

                            }

                            this.setAttribute(
                                'aria-label',
                                isPassword
                                    ? 'إخفاء كلمة المرور'
                                    : 'إظهار كلمة المرور'
                            );

                            this.setAttribute(
                                'title',
                                isPassword
                                    ? 'إخفاء كلمة المرور'
                                    : 'إظهار كلمة المرور'
                            );

                        }
                    );

                }


                /* =========================================================
                 * EMAIL VALIDATION
                 * ========================================================= */

                function validateEmail() {

                    if (!emailInput) {
                        return true;
                    }

                    const email =
                        emailInput.value.trim();

                    if (!email) {

                        emailHint.textContent =
                            'يرجى إدخال البريد الإلكتروني.';

                        emailHint.classList.remove(
                            'hidden'
                        );

                        return false;
                    }

                    if (!emailInput.checkValidity()) {

                        emailHint.textContent =
                            'يرجى إدخال بريد إلكتروني صحيح.';

                        emailHint.classList.remove(
                            'hidden'
                        );

                        return false;
                    }

                    emailHint.classList.add(
                        'hidden'
                    );

                    return true;
                }


                /* =========================================================
                 * PASSWORD VALIDATION
                 * ========================================================= */

                function validatePassword() {

                    if (!passwordInput) {
                        return true;
                    }

                    if (!passwordInput.value.trim()) {

                        passwordHint.textContent =
                            'يرجى إدخال كلمة المرور.';

                        passwordHint.classList.remove(
                            'hidden'
                        );

                        return false;
                    }

                    passwordHint.classList.add(
                        'hidden'
                    );

                    return true;
                }


                if (emailInput) {

                    emailInput.addEventListener(
                        'blur',
                        validateEmail
                    );

                    emailInput.addEventListener(
                        'input',
                        function () {

                            if (
                                !emailHint.classList.contains(
                                    'hidden'
                                )
                            ) {
                                validateEmail();
                            }

                        }
                    );

                }


                if (passwordInput) {

                    passwordInput.addEventListener(
                        'blur',
                        validatePassword
                    );

                    passwordInput.addEventListener(
                        'input',
                        function () {

                            if (
                                !passwordHint.classList.contains(
                                    'hidden'
                                )
                            ) {
                                validatePassword();
                            }

                        }
                    );

                }


                /* =========================================================
                 * FORM SUBMIT
                 * ========================================================= */

                let submitted = false;

                if (form) {

                    form.addEventListener(
                        'submit',
                        function (event) {

                            const emailValid =
                                validateEmail();

                            const passwordValid =
                                validatePassword();

                            if (
                                !emailValid ||
                                !passwordValid
                            ) {

                                event.preventDefault();

                                if (!emailValid && emailInput) {
                                    emailInput.focus();
                                } else if (
                                    !passwordValid &&
                                    passwordInput
                                ) {
                                    passwordInput.focus();
                                }

                                return;
                            }


                            /*
                             * Prevent accidental double submission.
                             */

                            if (submitted) {
                                event.preventDefault();
                                return;
                            }

                            submitted = true;


                            if (loginButton) {

                                loginButton.disabled = true;

                                loginButton.classList.add(
                                    'opacity-75',
                                    'cursor-wait'
                                );
                            }


                            if (loginButtonText) {
                                loginButtonText.textContent =
                                    'جاري الدخول...';
                            }


                            if (loginButton) {

                                const icon =
                                    loginButton.querySelector(
                                        'i'
                                    );

                                if (icon) {

                                    icon.classList.remove(
                                        'fa-right-to-bracket'
                                    );

                                    icon.classList.add(
                                        'fa-spinner',
                                        'fa-spin'
                                    );

                                }

                            }


                            if (loginLoading) {

                                loginLoading.classList.remove(
                                    'hidden'
                                );

                                loginLoading.classList.add(
                                    'flex'
                                );

                            }

                        }
                    );

                }


                /* =========================================================
                 * ENTER KEY
                 * ========================================================= */

                document.addEventListener(
                    'keydown',
                    function (event) {

                        if (
                            event.key !== 'Enter' ||
                            event.ctrlKey ||
                            event.altKey ||
                            event.shiftKey
                        ) {
                            return;
                        }

                        const activeElement =
                            document.activeElement;

                        if (
                            activeElement === emailInput ||
                            activeElement === passwordInput
                        ) {
                            return;
                        }

                    }
                );


                /* =========================================================
                 * REMEMBER ME
                 * ========================================================= */

                const rememberCheckbox =
                    document.querySelector(
                        'input[name="remember"]'
                    );

                if (rememberCheckbox) {

                    rememberCheckbox.addEventListener(
                        'change',
                        function () {

                            /*
                             * No browser storage is used here.
                             * Laravel remains responsible for the
                             * actual remember-me behavior.
                             */

                        }
                    );

                }


                /* =========================================================
                 * FOCUS EMAIL ON PAGE LOAD
                 * ========================================================= */

                if (
                    emailInput &&
                    !emailInput.value
                ) {

                    setTimeout(() => {
                        emailInput.focus();
                    }, 100);

                }

            });
        </script>
    @endpush

@endsection