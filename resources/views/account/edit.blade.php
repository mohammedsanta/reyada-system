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

        <div class="flex-1 min-w-[220px]">
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-xl font-bold text-fg">
                    أحمد محمد
                </h2>

                {{-- Online indicator --}}
                <span
                    class="inline-flex items-center gap-1.5 rounded-full border border-success/20 bg-success/5 px-2 py-1 text-[10px] font-semibold text-success"
                    title="الحساب نشط"
                >
                    <span class="h-1.5 w-1.5 rounded-full bg-success"></span>
                    نشط
                </span>
            </div>

            <div class="mt-2 flex flex-wrap items-center gap-2">
                <span class="badge badge-outline-info">
                    مدير النظام
                </span>

                <span class="badge badge-neutral font-mono">
                    USR-001
                </span>

                <span class="badge badge-outline-success">
                    <i class="fa-solid fa-shield-halved ml-1 text-[9px]"></i>
                    حساب موثوق
                </span>
            </div>

            <div class="mt-3 flex flex-wrap items-center gap-4 text-[11px] text-dim">
                <span class="inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-calendar-check text-info"></i>
                    عضو منذ:
                    <span class="text-muted">
                        15/01/2026
                    </span>
                </span>

                <span class="inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-clock text-warning"></i>
                    الجلسة الحالية نشطة
                </span>
            </div>
        </div>

        <div class="text-left text-[11px] text-dim">

            <p class="flex items-center justify-end gap-1.5">
                <i class="fa-solid fa-circle-check text-success"></i>
                آخر دخول:
                <span class="text-muted">
                    03/10/2026 08:30 PM
                </span>
            </p>

            <p class="mt-2 flex items-center justify-end gap-1.5">
                <i class="fa-solid fa-network-wired text-info"></i>
                عنوان IP:
                <button
                    type="button"
                    id="copyIpButton"
                    class="font-mono text-muted transition hover:text-info"
                    title="نسخ عنوان IP"
                    data-copy="192.168.1.100"
                >
                    192.168.1.100
                </button>
            </p>

            <p
                id="copyIpStatus"
                class="mt-1 hidden text-[10px] text-success"
            >
                تم نسخ عنوان IP
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

                {{-- Account completion --}}
                <div class="rounded-xl border border-border bg-card p-3">

                    <div class="flex items-center justify-between gap-3">

                        <div>
                            <p class="text-xs font-bold text-fg">
                                اكتمال بيانات الحساب
                            </p>

                            <p class="mt-1 text-[10px] text-dim">
                                بيانات الحساب الأساسية مكتملة.
                            </p>
                        </div>

                        <span class="text-sm font-bold text-success">
                            100%
                        </span>

                    </div>

                    <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-black/20">
                        <div
                            class="h-full w-full rounded-full bg-success"
                        ></div>
                    </div>

                </div>


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

                    <div class="relative">

                        <input
                            type="email"
                            value="ahmed@example.com"
                            dir="ltr"
                            class="form-input pl-10 opacity-60"
                            readonly
                        >

                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-success">
                            <i class="fa-solid fa-circle-check text-xs"></i>
                        </span>

                    </div>

                    <div class="mt-1 flex flex-wrap items-center justify-between gap-2">

                        <p class="text-[11px] text-dim">
                            لتغيير البريد الإلكتروني تواصل مع المدير.
                        </p>

                        <span class="inline-flex items-center gap-1 text-[10px] text-success">
                            <i class="fa-solid fa-check"></i>
                            بريد موثق
                        </span>

                    </div>
                </div>


                {{-- Account information --}}
                <div class="rounded-xl border border-border bg-card">

                    <div class="border-b border-border px-4 py-3">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-info text-info text-xs"></i>

                            <h3 class="text-xs font-bold text-fg">
                                معلومات الحساب
                            </h3>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 p-4 sm:grid-cols-2">

                        <div>
                            <p class="text-[10px] text-dim">
                                رقم المستخدم
                            </p>

                            <p class="mt-1 font-mono text-xs font-semibold text-fg">
                                USR-001
                            </p>
                        </div>

                        <div>
                            <p class="text-[10px] text-dim">
                                الدور
                            </p>

                            <p class="mt-1 text-xs font-semibold text-fg">
                                مدير النظام
                            </p>
                        </div>

                        <div>
                            <p class="text-[10px] text-dim">
                                حالة الحساب
                            </p>

                            <p class="mt-1 inline-flex items-center gap-1.5 text-xs font-semibold text-success">
                                <span class="h-1.5 w-1.5 rounded-full bg-success"></span>
                                نشط
                            </p>
                        </div>

                        <div>
                            <p class="text-[10px] text-dim">
                                مستوى الوصول
                            </p>

                            <p class="mt-1 text-xs font-semibold text-fg">
                                صلاحيات إدارية
                            </p>
                        </div>

                    </div>

                </div>


                {{-- Static button --}}
                <button
                    type="button"
                    id="saveProfileButton"
                    class="btn btn-primary"
                >
                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                    حفظ
                </button>

                <p
                    id="profileSaveStatus"
                    class="hidden text-[10px] text-success"
                >
                    <i class="fa-solid fa-check ml-1"></i>
                    تم حفظ التغييرات بنجاح.
                </p>

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

                    {{-- Security status --}}
                    <div class="rounded-xl border border-success/20 bg-success/5 p-3">

                        <div class="flex items-start gap-3">

                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-success/10 text-success">
                                <i class="fa-solid fa-shield-halved text-sm"></i>
                            </div>

                            <div class="min-w-0">
                                <p class="text-xs font-bold text-fg">
                                    أمان الحساب
                                </p>

                                <p class="mt-1 text-[10px] leading-5 text-dim">
                                    حافظ على كلمة مرور قوية ولا تشاركها مع أي شخص.
                                </p>
                            </div>

                            <span class="mr-auto whitespace-nowrap text-[10px] font-bold text-success">
                                جيد
                            </span>

                        </div>

                    </div>


                    <x-form-field
                        name="current_password"
                        label="كلمة المرور الحالية"
                        type="password"
                        dir="ltr"
                        autocomplete="current-password"
                        required
                    />


                    {{-- New password with visibility --}}
                    <div>
                        <label class="form-label">
                            كلمة المرور الجديدة
                        </label>

                        <div class="relative">

                            <input
                                id="newPassword"
                                type="password"
                                name="password"
                                dir="ltr"
                                autocomplete="new-password"
                                required
                                class="form-input pl-11"
                            >

                            <button
                                type="button"
                                id="toggleNewPassword"
                                class="absolute left-3 top-1/2 -translate-y-1/2 text-dim transition hover:text-fg"
                                title="إظهار كلمة المرور"
                                aria-label="إظهار كلمة المرور"
                            >
                                <i class="fa-solid fa-eye text-xs"></i>
                            </button>

                        </div>
                    </div>


                    {{-- Password strength --}}
                    <div
                        id="passwordStrengthBox"
                        class="hidden rounded-xl border border-border bg-card p-3"
                    >

                        <div class="flex items-center justify-between gap-3">

                            <span class="text-[10px] text-dim">
                                قوة كلمة المرور
                            </span>

                            <span
                                id="passwordStrengthLabel"
                                class="text-[10px] font-bold text-muted"
                            >
                                —
                            </span>

                        </div>

                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-black/20">
                            <div
                                id="passwordStrengthBar"
                                class="h-full w-0 rounded-full transition-all duration-300"
                            ></div>
                        </div>

                    </div>


                    {{-- Password requirements --}}
                    <div class="rounded-xl border border-border bg-card p-3">

                        <p class="text-[10px] font-bold text-fg">
                            متطلبات كلمة المرور
                        </p>

                        <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">

                            <div
                                id="ruleLength"
                                class="flex items-center gap-2 text-[10px] text-dim"
                            >
                                <i class="fa-solid fa-circle text-[5px]"></i>
                                8 أحرف على الأقل
                            </div>

                            <div
                                id="ruleUpper"
                                class="flex items-center gap-2 text-[10px] text-dim"
                            >
                                <i class="fa-solid fa-circle text-[5px]"></i>
                                حرف كبير واحد على الأقل
                            </div>

                            <div
                                id="ruleNumber"
                                class="flex items-center gap-2 text-[10px] text-dim"
                            >
                                <i class="fa-solid fa-circle text-[5px]"></i>
                                رقم واحد على الأقل
                            </div>

                            <div
                                id="ruleSpecial"
                                class="flex items-center gap-2 text-[10px] text-dim"
                            >
                                <i class="fa-solid fa-circle text-[5px]"></i>
                                رمز خاص واحد على الأقل
                            </div>

                        </div>

                    </div>


                    {{-- Confirmation with visibility --}}
                    <div>
                        <label class="form-label">
                            تأكيد كلمة المرور الجديدة
                        </label>

                        <div class="relative">

                            <input
                                id="passwordConfirmation"
                                type="password"
                                name="password_confirmation"
                                dir="ltr"
                                autocomplete="new-password"
                                required
                                class="form-input pl-11"
                            >

                            <button
                                type="button"
                                id="togglePasswordConfirmation"
                                class="absolute left-3 top-1/2 -translate-y-1/2 text-dim transition hover:text-fg"
                                title="إظهار كلمة المرور"
                                aria-label="إظهار كلمة المرور"
                            >
                                <i class="fa-solid fa-eye text-xs"></i>
                            </button>

                        </div>

                        <p
                            id="passwordMatchMessage"
                            class="mt-1 hidden text-[10px]"
                        ></p>
                    </div>


                    <p class="text-[11px] leading-5 text-dim">
                        8 أحرف على الأقل، ومختلفة عن كلمة المرور الحالية.
                    </p>


                    {{-- Security tips --}}
                    <div class="rounded-xl border border-warning/20 bg-warning/5 p-3">

                        <div class="flex items-start gap-2">

                            <i class="fa-solid fa-lightbulb mt-0.5 text-warning text-xs"></i>

                            <div>
                                <p class="text-[10px] font-bold text-fg">
                                    نصائح أمنية
                                </p>

                                <ul class="mt-1 space-y-1 text-[10px] leading-5 text-dim">
                                    <li>• لا تستخدم كلمة مرور مستخدمة في حساب آخر.</li>
                                    <li>• تجنب اسمك أو رقم هاتفك داخل كلمة المرور.</li>
                                    <li>• لا تشارك كلمة المرور مع أي شخص.</li>
                                </ul>
                            </div>

                        </div>

                    </div>


                    {{-- Static button --}}
                    <button
                        type="button"
                        id="changePasswordButton"
                        class="btn btn-primary"
                    >
                        <i class="fa-solid fa-key text-xs"></i>
                        تغيير كلمة المرور
                    </button>

                    <p
                        id="passwordChangeStatus"
                        class="hidden text-[10px] text-success"
                    >
                        <i class="fa-solid fa-check ml-1"></i>
                        تم تغيير كلمة المرور بنجاح.
                    </p>

                </div>

            </x-section-card>

        </div>

    </section>


    {{-- Logout --}}
    <div class="card mt-6 flex flex-wrap items-center justify-between gap-4 border-danger/20">

        <div class="min-w-[220px]">

            <div class="flex items-center gap-2">

                <h3 class="text-sm font-bold text-fg">
                    تسجيل الخروج
                </h3>

                <span class="badge badge-outline-danger">
                    جلسة حالية
                </span>

            </div>

            <p class="mt-1 text-xs text-muted">
                أنهِ جلستك الحالية على هذا الجهاز.
            </p>

            <div class="mt-3 flex flex-wrap gap-3 text-[10px] text-dim">

                <span class="inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-desktop text-info"></i>
                    جهاز حالي
                </span>

                <span class="inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-location-dot text-warning"></i>
                    192.168.1.100
                </span>

                <span class="inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-clock text-success"></i>
                    نشطة الآن
                </span>

            </div>
        </div>

        {{-- Static button --}}
        <button
            type="button"
            id="logoutButton"
            class="btn btn-danger"
        >
            <i class="fa-solid fa-right-from-bracket text-xs"></i>
            تسجيل الخروج
        </button>

    </div>


    {{-- Account activity / security --}}
    <div class="card mt-6">

        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-border px-4 py-4">

            <div class="flex items-center gap-3">

                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-info/10 text-info">
                    <i class="fa-solid fa-shield-halved text-sm"></i>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-fg">
                        نشاط وأمان الحساب
                    </h3>

                    <p class="mt-1 text-[10px] text-dim">
                        ملخص آخر الأنشطة الأمنية على الحساب.
                    </p>
                </div>

            </div>

            <span class="badge badge-outline-success">
                <i class="fa-solid fa-circle-check ml-1 text-[9px]"></i>
                لا توجد تنبيهات
            </span>

        </div>


        <div class="grid grid-cols-1 divide-y divide-border sm:grid-cols-2 sm:divide-x sm:divide-y-0 rtl:sm:divide-x-reverse">

            {{-- Last login --}}
            <div class="flex items-start gap-3 p-4">

                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-success/10 text-success">
                    <i class="fa-solid fa-right-to-bracket text-xs"></i>
                </div>

                <div class="min-w-0">

                    <p class="text-xs font-semibold text-fg">
                        آخر تسجيل دخول
                    </p>

                    <p class="mt-1 text-[10px] text-dim">
                        03/10/2026 08:30 PM
                    </p>

                    <p class="mt-1 font-mono text-[10px] text-muted" dir="ltr">
                        192.168.1.100
                    </p>

                </div>

            </div>


            {{-- Password --}}
            <div class="flex items-start gap-3 p-4">

                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-warning/10 text-warning">
                    <i class="fa-solid fa-key text-xs"></i>
                </div>

                <div class="min-w-0">

                    <p class="text-xs font-semibold text-fg">
                        كلمة المرور
                    </p>

                    <p class="mt-1 text-[10px] text-dim">
                        آخر تغيير منذ فترة.
                    </p>

                    <a
                        href="#password"
                        class="mt-1 inline-flex items-center gap-1 text-[10px] text-warning transition hover:text-warning/80"
                    >
                        تغيير كلمة المرور
                        <i class="fa-solid fa-arrow-left text-[8px]"></i>
                    </a>

                </div>

            </div>

        </div>

    </div>


    {{-- Back to top --}}
    <button
        type="button"
        id="accountBackToTop"
        class="fixed bottom-6 left-6 z-40 hidden h-10 w-10 items-center justify-center rounded-full border border-border bg-panel text-dim shadow-lg transition hover:border-brand hover:text-brand"
        title="العودة للأعلى"
        aria-label="العودة للأعلى"
    >
        <i class="fa-solid fa-arrow-up text-xs"></i>
    </button>


    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {

                /* =========================================================
                 * COPY IP
                 * ========================================================= */

                const copyIpButton = document.getElementById('copyIpButton');
                const copyIpStatus = document.getElementById('copyIpStatus');

                if (copyIpButton) {
                    copyIpButton.addEventListener('click', async function () {

                        const value = this.dataset.copy || this.textContent.trim();

                        try {
                            await navigator.clipboard.writeText(value);

                            if (copyIpStatus) {
                                copyIpStatus.classList.remove('hidden');

                                setTimeout(() => {
                                    copyIpStatus.classList.add('hidden');
                                }, 1800);
                            }

                        } catch (error) {
                            console.warn('Unable to copy IP address.', error);
                        }

                    });
                }


                /* =========================================================
                 * PASSWORD VISIBILITY
                 * ========================================================= */

                function setupPasswordToggle(buttonId, inputId) {

                    const button = document.getElementById(buttonId);
                    const input = document.getElementById(inputId);

                    if (!button || !input) {
                        return;
                    }

                    button.addEventListener('click', function () {

                        const isPassword = input.type === 'password';

                        input.type = isPassword ? 'text' : 'password';

                        const icon = this.querySelector('i');

                        if (icon) {
                            icon.classList.toggle('fa-eye', !isPassword);
                            icon.classList.toggle('fa-eye-slash', isPassword);
                        }

                        this.setAttribute(
                            'aria-label',
                            isPassword
                                ? 'إخفاء كلمة المرور'
                                : 'إظهار كلمة المرور'
                        );

                    });
                }

                setupPasswordToggle(
                    'toggleNewPassword',
                    'newPassword'
                );

                setupPasswordToggle(
                    'togglePasswordConfirmation',
                    'passwordConfirmation'
                );


                /* =========================================================
                 * PASSWORD STRENGTH
                 * ========================================================= */

                const newPassword = document.getElementById('newPassword');
                const passwordStrengthBox = document.getElementById('passwordStrengthBox');
                const passwordStrengthBar = document.getElementById('passwordStrengthBar');
                const passwordStrengthLabel = document.getElementById('passwordStrengthLabel');

                const ruleLength = document.getElementById('ruleLength');
                const ruleUpper = document.getElementById('ruleUpper');
                const ruleNumber = document.getElementById('ruleNumber');
                const ruleSpecial = document.getElementById('ruleSpecial');

                function updateRule(element, valid) {

                    if (!element) {
                        return;
                    }

                    const icon = element.querySelector('i');

                    element.classList.remove(
                        'text-dim',
                        'text-success'
                    );

                    element.classList.add(
                        valid ? 'text-success' : 'text-dim'
                    );

                    if (icon) {

                        icon.classList.remove(
                            'fa-circle',
                            'fa-circle-check'
                        );

                        icon.classList.add(
                            valid
                                ? 'fa-circle-check'
                                : 'fa-circle'
                        );
                    }
                }


                function calculatePasswordStrength(password) {

                    if (!password) {
                        return {
                            score: 0,
                            label: '—'
                        };
                    }

                    let score = 0;

                    if (password.length >= 8) {
                        score++;
                    }

                    if (/[A-Z]/.test(password)) {
                        score++;
                    }

                    if (/[0-9]/.test(password)) {
                        score++;
                    }

                    if (/[^A-Za-z0-9]/.test(password)) {
                        score++;
                    }

                    if (password.length >= 12) {
                        score++;
                    }

                    if (score <= 1) {
                        return {
                            score: 1,
                            label: 'ضعيفة'
                        };
                    }

                    if (score <= 3) {
                        return {
                            score: 2,
                            label: 'متوسطة'
                        };
                    }

                    if (score === 4) {
                        return {
                            score: 3,
                            label: 'قوية'
                        };
                    }

                    return {
                        score: 4,
                        label: 'قوية جداً'
                    };
                }


                function updatePasswordStrength() {

                    if (!newPassword) {
                        return;
                    }

                    const password = newPassword.value;

                    if (!password) {

                        passwordStrengthBox?.classList.add('hidden');

                        updateRule(ruleLength, false);
                        updateRule(ruleUpper, false);
                        updateRule(ruleNumber, false);
                        updateRule(ruleSpecial, false);

                        return;
                    }

                    passwordStrengthBox?.classList.remove('hidden');

                    const lengthValid = password.length >= 8;
                    const upperValid = /[A-Z]/.test(password);
                    const numberValid = /[0-9]/.test(password);
                    const specialValid = /[^A-Za-z0-9]/.test(password);

                    updateRule(ruleLength, lengthValid);
                    updateRule(ruleUpper, upperValid);
                    updateRule(ruleNumber, numberValid);
                    updateRule(ruleSpecial, specialValid);

                    const strength = calculatePasswordStrength(password);

                    if (passwordStrengthLabel) {
                        passwordStrengthLabel.textContent = strength.label;
                    }

                    if (passwordStrengthBar) {

                        const widths = {
                            1: '25%',
                            2: '50%',
                            3: '75%',
                            4: '100%'
                        };

                        passwordStrengthBar.style.width =
                            widths[strength.score] || '0%';
                    }

                    if (passwordStrengthLabel) {

                        passwordStrengthLabel.classList.remove(
                            'text-danger',
                            'text-warning',
                            'text-success'
                        );

                        if (strength.score <= 1) {
                            passwordStrengthLabel.classList.add('text-danger');
                        } else if (strength.score <= 2) {
                            passwordStrengthLabel.classList.add('text-warning');
                        } else {
                            passwordStrengthLabel.classList.add('text-success');
                        }
                    }

                }

                if (newPassword) {
                    newPassword.addEventListener(
                        'input',
                        updatePasswordStrength
                    );
                }


                /* =========================================================
                 * PASSWORD CONFIRMATION
                 * ========================================================= */

                const passwordConfirmation =
                    document.getElementById('passwordConfirmation');

                const passwordMatchMessage =
                    document.getElementById('passwordMatchMessage');

                function checkPasswordMatch() {

                    if (!passwordConfirmation || !newPassword) {
                        return;
                    }

                    const password = newPassword.value;
                    const confirmation = passwordConfirmation.value;

                    if (!confirmation) {

                        passwordMatchMessage?.classList.add('hidden');

                        return;
                    }

                    passwordMatchMessage?.classList.remove('hidden');

                    if (password === confirmation) {

                        passwordMatchMessage.classList.remove(
                            'text-danger'
                        );

                        passwordMatchMessage.classList.add(
                            'text-success'
                        );

                        passwordMatchMessage.innerHTML =
                            '<i class="fa-solid fa-circle-check ml-1"></i> كلمتا المرور متطابقتان.';

                    } else {

                        passwordMatchMessage.classList.remove(
                            'text-success'
                        );

                        passwordMatchMessage.classList.add(
                            'text-danger'
                        );

                        passwordMatchMessage.innerHTML =
                            '<i class="fa-solid fa-circle-xmark ml-1"></i> كلمتا المرور غير متطابقتين.';

                    }
                }

                if (newPassword) {
                    newPassword.addEventListener(
                        'input',
                        checkPasswordMatch
                    );
                }

                if (passwordConfirmation) {
                    passwordConfirmation.addEventListener(
                        'input',
                        checkPasswordMatch
                    );
                }


                /* =========================================================
                 * PASSWORD BUTTON
                 * ========================================================= */

                const changePasswordButton =
                    document.getElementById('changePasswordButton');

                const passwordChangeStatus =
                    document.getElementById('passwordChangeStatus');

                if (changePasswordButton) {

                    changePasswordButton.addEventListener(
                        'click',
                        function () {

                            const currentPassword =
                                document.querySelector(
                                    '[name="current_password"]'
                                );

                            const password =
                                document.querySelector(
                                    '[name="password"]'
                                );

                            const confirmation =
                                document.querySelector(
                                    '[name="password_confirmation"]'
                                );

                            if (
                                !currentPassword ||
                                !password ||
                                !confirmation
                            ) {
                                return;
                            }

                            let valid = true;

                            if (!currentPassword.value.trim()) {
                                currentPassword.focus();
                                valid = false;
                            }

                            if (
                                valid &&
                                password.value.length < 8
                            ) {
                                password.focus();
                                valid = false;
                            }

                            if (
                                valid &&
                                password.value !== confirmation.value
                            ) {
                                confirmation.focus();
                                valid = false;
                            }

                            if (!valid) {
                                return;
                            }

                            /*
                             * The current page is still using a static button.
                             * This visual state can later be connected to the
                             * real update endpoint without changing the UI.
                             */

                            const originalHtml =
                                changePasswordButton.innerHTML;

                            changePasswordButton.disabled = true;

                            changePasswordButton.innerHTML =
                                '<i class="fa-solid fa-spinner fa-spin text-xs"></i> جارٍ التحقق...';

                            setTimeout(() => {

                                changePasswordButton.disabled = false;

                                changePasswordButton.innerHTML =
                                    originalHtml;

                                if (passwordChangeStatus) {
                                    passwordChangeStatus.classList.remove(
                                        'hidden'
                                    );

                                    setTimeout(() => {
                                        passwordChangeStatus.classList.add(
                                            'hidden'
                                        );
                                    }, 3000);
                                }

                            }, 700);

                        }
                    );
                }


                /* =========================================================
                 * PROFILE SAVE
                 * ========================================================= */

                const saveProfileButton =
                    document.getElementById('saveProfileButton');

                const profileSaveStatus =
                    document.getElementById('profileSaveStatus');

                if (saveProfileButton) {

                    saveProfileButton.addEventListener(
                        'click',
                        function () {

                            const originalHtml =
                                this.innerHTML;

                            this.disabled = true;

                            this.innerHTML =
                                '<i class="fa-solid fa-spinner fa-spin text-xs"></i> جارٍ الحفظ...';

                            setTimeout(() => {

                                this.disabled = false;

                                this.innerHTML =
                                    originalHtml;

                                profileSaveStatus?.classList.remove(
                                    'hidden'
                                );

                                setTimeout(() => {
                                    profileSaveStatus?.classList.add(
                                        'hidden'
                                    );
                                }, 3000);

                            }, 700);

                        }
                    );
                }


                /* =========================================================
                 * LOGOUT CONFIRMATION
                 * ========================================================= */

                const logoutButton =
                    document.getElementById('logoutButton');

                if (logoutButton) {

                    logoutButton.addEventListener(
                        'click',
                        function () {

                            const confirmed = window.confirm(
                                'هل أنت متأكد من تسجيل الخروج من حسابك؟'
                            );

                            if (!confirmed) {
                                return;
                            }

                            const originalHtml =
                                this.innerHTML;

                            this.disabled = true;

                            this.innerHTML =
                                '<i class="fa-solid fa-spinner fa-spin text-xs"></i> جارٍ تسجيل الخروج...';

                            /*
                             * The button remains frontend-only until the
                             * existing logout route/form is connected.
                             */

                            setTimeout(() => {

                                this.disabled = false;

                                this.innerHTML =
                                    originalHtml;

                            }, 1000);

                        }
                    );
                }


                /* =========================================================
                 * BACK TO TOP
                 * ========================================================= */

                const backToTop =
                    document.getElementById('accountBackToTop');

                function updateBackToTop() {

                    if (!backToTop) {
                        return;
                    }

                    if (window.scrollY > 400) {
                        backToTop.classList.remove('hidden');
                        backToTop.classList.add('flex');
                    } else {
                        backToTop.classList.add('hidden');
                        backToTop.classList.remove('flex');
                    }
                }

                window.addEventListener(
                    'scroll',
                    updateBackToTop,
                    { passive: true }
                );

                updateBackToTop();

                if (backToTop) {

                    backToTop.addEventListener(
                        'click',
                        function () {

                            window.scrollTo({
                                top: 0,
                                behavior: 'smooth'
                            });

                        }
                    );
                }


                /* =========================================================
                 * ESCAPE KEY
                 * ========================================================= */

                document.addEventListener(
                    'keydown',
                    function (event) {

                        if (event.key !== 'Escape') {
                            return;
                        }

                        /*
                         * Do not clear fields or submit anything.
                         * This is intentionally safe for the current
                         * static account page.
                         */

                    }
                );

            });
        </script>
    @endpush

@endsection