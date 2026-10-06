{{-- resources/views/ptp/create.blade.php --}}
@extends('layouts.app')

@section('title', 'إضافة وعد دفع - ' . $bank->name)

@php
    /*
    |--------------------------------------------------------------------------
    | Safe display values
    |--------------------------------------------------------------------------
    | These values are only used by the frontend.
    | They do not change your backend structure or routes.
    |--------------------------------------------------------------------------
    */

    $selectedClient = $selected ?? '';

    $tomorrowDate = $tomorrow ?? now()->addDay()->format('Y-m-d');

    $clientCount = collect($clients ?? [])->count();
    $employeeCount = collect($employees ?? [])->count();
    $methodCount = collect($methods ?? [])->count();
@endphp

@section('content')

    {{-- ================================================================ --}}
    {{-- PAGE HEADER --}}
    {{-- ================================================================ --}}

    <x-bank-header
        :bank="$bank"
        title="إضافة وعد دفع"
        subtitle="تسجيل وعد جديد من عميل"
        :back="route('banks.ptp.index', $bank->id)"
    />

    {{-- ================================================================ --}}
    {{-- FLASH / VALIDATION --}}
    {{-- ================================================================ --}}

    <x-flash />

    {{-- Laravel validation errors --}}
    @if($errors->any())

        <div class="mb-4 rounded-xl border border-danger/20 bg-danger/5 p-4">

            <div class="flex items-start gap-3">

                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-danger/10 text-danger">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </span>

                <div class="min-w-0">

                    <p class="text-sm font-bold text-fg">
                        يرجى مراجعة البيانات
                    </p>

                    <ul class="mt-2 space-y-1 text-xs leading-5 text-muted">

                        @foreach($errors->all() as $error)

                            <li class="flex items-start gap-2">
                                <i class="fa-solid fa-circle mt-1.5 text-[5px] text-danger"></i>
                                <span>{{ $error }}</span>
                            </li>

                        @endforeach

                    </ul>

                </div>

            </div>

        </div>

    @endif

    {{-- ================================================================ --}}
    {{-- QUICK SUMMARY --}}
    {{-- ================================================================ --}}

    <section class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-3">

        {{-- Bank --}}
        <div class="card">

            <div class="flex items-center justify-between gap-3">

                <div>
                    <p class="text-[11px] text-dim">
                        البنك
                    </p>

                    <p class="mt-1 truncate text-sm font-bold text-fg">
                        {{ $bank->name }}
                    </p>
                </div>

                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-info/10 text-info">
                    <i class="fa-solid fa-building-columns"></i>
                </span>

            </div>

        </div>

        {{-- Clients --}}
        <div class="card">

            <div class="flex items-center justify-between gap-3">

                <div>
                    <p class="text-[11px] text-dim">
                        العملاء المتاحون
                    </p>

                    <p class="mt-1 text-xl font-bold text-fg">
                        {{ number_format($clientCount) }}
                    </p>
                </div>

                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-info/10 text-info">
                    <i class="fa-solid fa-users"></i>
                </span>

            </div>

        </div>

        {{-- Employees --}}
        <div class="card">

            <div class="flex items-center justify-between gap-3">

                <div>
                    <p class="text-[11px] text-dim">
                        الموظفون المتاحون
                    </p>

                    <p class="mt-1 text-xl font-bold text-fg">
                        {{ number_format($employeeCount) }}
                    </p>
                </div>

                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand/10 text-brand">
                    <i class="fa-solid fa-user-tie"></i>
                </span>

            </div>

        </div>

    </section>

    {{-- ================================================================ --}}
    {{-- MAIN FORM --}}
    {{-- ================================================================ --}}

    <form
        id="ptpCreateForm"
        method="POST"
        action="{{ route('banks.ptp.store', $bank->id) }}"
        class="max-w-3xl space-y-4"
    >

        @csrf

        {{-- ============================================================ --}}
        {{-- Promise Data --}}
        {{-- ============================================================ --}}

        <x-section-card
            title="بيانات الوعد"
            icon="fa-handshake"
            color="warning"
        >

            {{-- Section introduction --}}
            <div class="mb-5 rounded-xl border border-warning/20 bg-warning/5 p-4">

                <div class="flex items-start gap-3">

                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-warning/10 text-warning">
                        <i class="fa-solid fa-circle-info"></i>
                    </span>

                    <div>

                        <p class="text-sm font-bold text-fg">
                            تسجيل وعد دفع جديد
                        </p>

                        <p class="mt-1 text-xs leading-5 text-muted">
                            أدخل بيانات العميل والموظف والمبلغ والتاريخ المتوقع للسداد.
                            سيتم حفظ الوعد تحت بنك {{ $bank->name }}.
                        </p>

                    </div>

                </div>

            </div>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">

                {{-- ==================================================== --}}
                {{-- Client --}}
                {{-- ==================================================== --}}

                <div class="md:col-span-1">

                    <x-select-field
                        name="client_code"
                        label="العميل"
                        :options="$clients"
                        :value="$selectedClient"
                        placeholder="اختر العميل..."
                    />

                    <div
                        id="clientSelectionHint"
                        class="mt-1 text-[10px] text-dim"
                    >
                        اختر العميل المرتبط بالوعد.
                    </div>

                </div>

                {{-- ==================================================== --}}
                {{-- Employee --}}
                {{-- ==================================================== --}}

                <div class="md:col-span-1">

                    <x-select-field
                        name="employee_id"
                        label="الموظف الذي أخذ الوعد"
                        :options="$employees"
                        placeholder="اختر الموظف..."
                    />

                    <div
                        class="mt-1 text-[10px] text-dim"
                    >
                        الموظف المسؤول عن تسجيل الوعد.
                    </div>

                </div>

                {{-- ==================================================== --}}
                {{-- Amount --}}
                {{-- ==================================================== --}}

                <div>

                    <x-form-field
                        name="amount"
                        label="المبلغ الموعود (EGP)"
                        type="number"
                        min="1"
                        step="0.01"
                        placeholder="مثال: 1500"
                    />

                    {{-- Live amount helper --}}
                    <div
                        id="amountPreview"
                        class="mt-1 hidden text-[10px] text-muted"
                    >
                        قيمة الوعد:
                        <span
                            id="amountPreviewValue"
                            class="font-bold text-fg"
                        >
                            EGP 0.00
                        </span>
                    </div>

                </div>

                {{-- ==================================================== --}}
                {{-- Promise Date --}}
                {{-- ==================================================== --}}

                <div>

                    <x-form-field
                        name="promise_date"
                        label="تاريخ السداد الموعود"
                        type="date"
                        :value="$tomorrowDate"
                    />

                    <div
                        id="dateHint"
                        class="mt-1 text-[10px] text-muted"
                    >
                        تاريخ السداد المتوقع من العميل.
                    </div>

                </div>

                {{-- ==================================================== --}}
                {{-- Method --}}
                {{-- ==================================================== --}}

                <div>

                    <x-select-field
                        name="method"
                        label="طريقة السداد المتوقعة"
                        :options="$methods"
                        placeholder="غير محددة"
                    />

                    <div class="mt-1 text-[10px] text-dim">
                        طريقة الدفع المتوقعة من العميل.
                    </div>

                </div>

            </div>

            {{-- ======================================================== --}}
            {{-- Notes --}}
            {{-- ======================================================== --}}

            <div class="mt-3">

                <x-textarea-field
                    name="notes"
                    label="ملاحظات"
                    rows="3"
                />

                <div class="mt-1 flex justify-end text-[10px] text-dim">
                    <span id="notesCounter">0</span>
                    / 1000
                </div>

            </div>

        </x-section-card>

        {{-- ============================================================ --}}
        {{-- LIVE PREVIEW --}}
        {{-- ============================================================ --}}

        <x-section-card
            title="معاينة الوعد"
            icon="fa-eye"
            color="info"
        >

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">

                {{-- Amount --}}
                <div class="rounded-xl border border-line bg-white/[0.02] p-3">

                    <p class="text-[10px] text-dim">
                        المبلغ
                    </p>

                    <p
                        id="previewAmount"
                        class="mt-1 text-lg font-bold text-fg"
                    >
                        EGP 0.00
                    </p>

                </div>

                {{-- Date --}}
                <div class="rounded-xl border border-line bg-white/[0.02] p-3">

                    <p class="text-[10px] text-dim">
                        تاريخ السداد
                    </p>

                    <p
                        id="previewDate"
                        class="mt-1 text-sm font-bold text-fg"
                    >
                        {{ $tomorrowDate }}
                    </p>

                </div>

                {{-- Method --}}
                <div class="rounded-xl border border-line bg-white/[0.02] p-3">

                    <p class="text-[10px] text-dim">
                        طريقة السداد
                    </p>

                    <p
                        id="previewMethod"
                        class="mt-1 text-sm font-bold text-fg"
                    >
                        غير محددة
                    </p>

                </div>

            </div>

            {{-- Date status --}}
            <div
                id="dateStatus"
                class="mt-3 hidden rounded-xl border p-3"
            >
                <div class="flex items-center gap-2">

                    <i
                        id="dateStatusIcon"
                        class="fa-solid fa-calendar-day text-xs"
                    ></i>

                    <span
                        id="dateStatusText"
                        class="text-xs font-semibold"
                    ></span>

                </div>
            </div>

        </x-section-card>

        {{-- ============================================================ --}}
        {{-- Registration Checklist --}}
        {{-- ============================================================ --}}

        <x-section-card
            title="مراجعة قبل التسجيل"
            icon="fa-list-check"
            color="brand"
        >

            <div class="space-y-2 text-xs">

                <div
                    id="checkClient"
                    class="flex items-center gap-3 rounded-xl border border-line bg-white/[0.02] p-3"
                >
                    <span class="check-icon flex h-7 w-7 items-center justify-center rounded-lg bg-white/5 text-dim">
                        <i class="fa-solid fa-user text-[10px]"></i>
                    </span>

                    <span class="check-text text-muted">
                        اختيار العميل
                    </span>
                </div>

                <div
                    id="checkEmployee"
                    class="flex items-center gap-3 rounded-xl border border-line bg-white/[0.02] p-3"
                >
                    <span class="check-icon flex h-7 w-7 items-center justify-center rounded-lg bg-white/5 text-dim">
                        <i class="fa-solid fa-user-tie text-[10px]"></i>
                    </span>

                    <span class="check-text text-muted">
                        اختيار الموظف
                    </span>
                </div>

                <div
                    id="checkAmount"
                    class="flex items-center gap-3 rounded-xl border border-line bg-white/[0.02] p-3"
                >
                    <span class="check-icon flex h-7 w-7 items-center justify-center rounded-lg bg-white/5 text-dim">
                        <i class="fa-solid fa-money-bill-wave text-[10px]"></i>
                    </span>

                    <span class="check-text text-muted">
                        إدخال المبلغ
                    </span>
                </div>

                <div
                    id="checkDate"
                    class="flex items-center gap-3 rounded-xl border border-line bg-white/[0.02] p-3"
                >
                    <span class="check-icon flex h-7 w-7 items-center justify-center rounded-lg bg-white/5 text-dim">
                        <i class="fa-solid fa-calendar text-[10px]"></i>
                    </span>

                    <span class="check-text text-muted">
                        تحديد تاريخ السداد
                    </span>
                </div>

            </div>

        </x-section-card>

        {{-- ============================================================ --}}
        {{-- ACTIONS --}}
        {{-- ============================================================ --}}

        <div class="flex flex-wrap items-center justify-between gap-3">

            <div class="flex gap-2">

                <button
                    id="submitPromiseButton"
                    type="submit"
                    class="btn btn-primary"
                >
                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                    تسجيل الوعد
                </button>

                <a
                    href="{{ route('banks.ptp.index', $bank->id) }}"
                    class="btn btn-secondary"
                >
                    إلغاء
                </a>

            </div>

            <button
                id="resetPromiseButton"
                type="button"
                class="btn btn-secondary"
            >
                <i class="fa-solid fa-rotate-left text-xs"></i>
                إعادة ضبط
            </button>

        </div>

        {{-- Keyboard hint --}}
        <p class="text-[10px] text-dim">
            <i class="fa-solid fa-keyboard ml-1"></i>
            يمكنك استخدام
            <kbd class="rounded border border-line bg-white/5 px-1.5 py-0.5 font-mono">
                Ctrl
            </kbd>
            +
            <kbd class="rounded border border-line bg-white/5 px-1.5 py-0.5 font-mono">
                Enter
            </kbd>
            لتسجيل الوعد بسرعة.
        </p>

    </form>

    {{-- ================================================================ --}}
    {{-- BACK TO TOP --}}
    {{-- ================================================================ --}}

    <button
        id="ptpCreateBackToTop"
        type="button"
        class="fixed bottom-6 left-6 z-50 hidden h-10 w-10 items-center justify-center rounded-xl border border-line bg-surface text-muted shadow-lg transition hover:text-fg"
        title="العودة للأعلى"
        aria-label="العودة للأعلى"
    >
        <i class="fa-solid fa-arrow-up text-xs"></i>
    </button>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            /*
            |--------------------------------------------------------------------------
            | Form elements
            |--------------------------------------------------------------------------
            */

            const form = document.getElementById('ptpCreateForm');

            if (!form) {
                return;
            }

            const client = document.getElementById('client_code');
            const employee = document.getElementById('employee_id');
            const amount = document.getElementById('amount');
            const promiseDate = document.getElementById('promise_date');
            const method = document.getElementById('method');
            const notes = document.getElementById('notes');

            const submitButton =
                document.getElementById('submitPromiseButton');

            const resetButton =
                document.getElementById('resetPromiseButton');

            /*
            |--------------------------------------------------------------------------
            | Preview elements
            |--------------------------------------------------------------------------
            */

            const previewAmount =
                document.getElementById('previewAmount');

            const amountPreview =
                document.getElementById('amountPreview');

            const amountPreviewValue =
                document.getElementById('amountPreviewValue');

            const previewDate =
                document.getElementById('previewDate');

            const previewMethod =
                document.getElementById('previewMethod');

            const dateHint =
                document.getElementById('dateHint');

            const dateStatus =
                document.getElementById('dateStatus');

            const dateStatusIcon =
                document.getElementById('dateStatusIcon');

            const dateStatusText =
                document.getElementById('dateStatusText');

            const notesCounter =
                document.getElementById('notesCounter');

            /*
            |--------------------------------------------------------------------------
            | Checklist elements
            |--------------------------------------------------------------------------
            */

            const checkClient =
                document.getElementById('checkClient');

            const checkEmployee =
                document.getElementById('checkEmployee');

            const checkAmount =
                document.getElementById('checkAmount');

            const checkDate =
                document.getElementById('checkDate');

            /*
            |--------------------------------------------------------------------------
            | Back to top
            |--------------------------------------------------------------------------
            */

            const backToTop =
                document.getElementById('ptpCreateBackToTop');

            /*
            |--------------------------------------------------------------------------
            | Format money
            |--------------------------------------------------------------------------
            */

            const formatMoney = function (value) {

                return new Intl.NumberFormat('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(value);

            };

            /*
            |--------------------------------------------------------------------------
            | Format date
            |--------------------------------------------------------------------------
            */

            const formatDateArabic = function (dateValue) {

                if (!dateValue) {
                    return 'غير محدد';
                }

                const date = new Date(
                    dateValue + 'T00:00:00'
                );

                if (Number.isNaN(date.getTime())) {
                    return dateValue;
                }

                return new Intl.DateTimeFormat('ar-EG', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                }).format(date);

            };

            /*
            |--------------------------------------------------------------------------
            | Today's date
            |--------------------------------------------------------------------------
            */

            const getToday = function () {

                const date = new Date();

                const year = date.getFullYear();

                const month = String(
                    date.getMonth() + 1
                ).padStart(2, '0');

                const day = String(
                    date.getDate()
                ).padStart(2, '0');

                return `${year}-${month}-${day}`;
            };

            /*
            |--------------------------------------------------------------------------
            | Checklist state
            |--------------------------------------------------------------------------
            */

            const updateCheck = function (
                element,
                valid
            ) {

                if (!element) {
                    return;
                }

                const icon =
                    element.querySelector('.check-icon');

                const text =
                    element.querySelector('.check-text');

                if (valid) {

                    element.classList.remove(
                        'border-line'
                    );

                    element.classList.add(
                        'border-brand/20',
                        'bg-brand/5'
                    );

                    if (icon) {

                        icon.classList.remove(
                            'bg-white/5',
                            'text-dim'
                        );

                        icon.classList.add(
                            'bg-brand/10',
                            'text-brand'
                        );

                        icon.innerHTML =
                            '<i class="fa-solid fa-check text-[10px]"></i>';
                    }

                    if (text) {
                        text.classList.remove(
                            'text-muted'
                        );

                        text.classList.add(
                            'text-fg'
                        );
                    }

                } else {

                    element.classList.remove(
                        'border-brand/20',
                        'bg-brand/5'
                    );

                    element.classList.add(
                        'border-line',
                        'bg-white/[0.02]'
                    );

                    if (icon) {

                        icon.classList.remove(
                            'bg-brand/10',
                            'text-brand'
                        );

                        icon.classList.add(
                            'bg-white/5',
                            'text-dim'
                        );
                    }

                    if (text) {

                        text.classList.remove(
                            'text-fg'
                        );

                        text.classList.add(
                            'text-muted'
                        );
                    }

                }
            };

            /*
            |--------------------------------------------------------------------------
            | Update amount preview
            |--------------------------------------------------------------------------
            */

            const updateAmount = function () {

                if (!amount) {
                    return;
                }

                const value = Number(
                    amount.value || 0
                );

                if (value > 0) {

                    const formatted =
                        'EGP ' + formatMoney(value);

                    if (previewAmount) {
                        previewAmount.textContent =
                            formatted;
                    }

                    if (amountPreview) {
                        amountPreview.classList.remove(
                            'hidden'
                        );
                    }

                    if (amountPreviewValue) {
                        amountPreviewValue.textContent =
                            formatted;
                    }

                    updateCheck(
                        checkAmount,
                        true
                    );

                } else {

                    if (previewAmount) {
                        previewAmount.textContent =
                            'EGP 0.00';
                    }

                    if (amountPreview) {
                        amountPreview.classList.add(
                            'hidden'
                        );
                    }

                    updateCheck(
                        checkAmount,
                        false
                    );
                }
            };

            /*
            |--------------------------------------------------------------------------
            | Update date preview
            |--------------------------------------------------------------------------
            */

            const updateDate = function () {

                if (!promiseDate) {
                    return;
                }

                const value =
                    promiseDate.value;

                if (previewDate) {

                    previewDate.textContent =
                        formatDateArabic(value);
                }

                if (!value) {

                    updateCheck(
                        checkDate,
                        false
                    );

                    if (dateStatus) {
                        dateStatus.classList.add(
                            'hidden'
                        );
                    }

                    return;
                }

                updateCheck(
                    checkDate,
                    true
                );

                const today = getToday();

                /*
                | Past date
                */
                if (value < today) {

                    if (dateStatus) {

                        dateStatus.classList.remove(
                            'hidden'
                        );

                        dateStatus.classList.remove(
                            'border-brand/20',
                            'bg-brand/5',
                            'border-info/20',
                            'bg-info/5'
                        );

                        dateStatus.classList.add(
                            'border-danger/20',
                            'bg-danger/5'
                        );
                    }

                    if (dateStatusIcon) {

                        dateStatusIcon.className =
                            'fa-solid fa-triangle-exclamation text-xs text-danger';
                    }

                    if (dateStatusText) {

                        dateStatusText.className =
                            'text-xs font-semibold text-danger';

                        dateStatusText.textContent =
                            'التاريخ المختار سابق لتاريخ اليوم.';
                    }

                    if (dateHint) {

                        dateHint.classList.remove(
                            'text-muted',
                            'text-brand'
                        );

                        dateHint.classList.add(
                            'text-danger'
                        );
                    }

                    return;
                }

                /*
                | Today
                */
                if (value === today) {

                    if (dateStatus) {

                        dateStatus.classList.remove(
                            'hidden'
                        );

                        dateStatus.classList.remove(
                            'border-danger/20',
                            'bg-danger/5',
                            'border-info/20',
                            'bg-info/5'
                        );

                        dateStatus.classList.add(
                            'border-info/20',
                            'bg-info/5'
                        );
                    }

                    if (dateStatusIcon) {

                        dateStatusIcon.className =
                            'fa-solid fa-calendar-day text-xs text-info';
                    }

                    if (dateStatusText) {

                        dateStatusText.className =
                            'text-xs font-semibold text-info';

                        dateStatusText.textContent =
                            'موعد السداد هو اليوم.';
                    }

                    if (dateHint) {

                        dateHint.classList.remove(
                            'text-danger',
                            'text-brand'
                        );

                        dateHint.classList.add(
                            'text-info'
                        );
                    }

                    return;
                }

                /*
                | Future
                */
                if (dateStatus) {

                    dateStatus.classList.remove(
                        'hidden'
                    );

                    dateStatus.classList.remove(
                        'border-danger/20',
                        'bg-danger/5',
                        'border-info/20',
                        'bg-info/5'
                    );

                    dateStatus.classList.add(
                        'border-brand/20',
                        'bg-brand/5'
                    );
                }

                if (dateStatusIcon) {

                    dateStatusIcon.className =
                        'fa-solid fa-calendar-check text-xs text-brand';
                }

                if (dateStatusText) {

                    dateStatusText.className =
                        'text-xs font-semibold text-brand';

                    dateStatusText.textContent =
                        'موعد السداد في المستقبل: ' +
                        formatDateArabic(value);
                }

                if (dateHint) {

                    dateHint.classList.remove(
                        'text-danger',
                        'text-info'
                    );

                    dateHint.classList.add(
                        'text-brand'
                    );
                }
            };

            /*
            |--------------------------------------------------------------------------
            | Update method preview
            |--------------------------------------------------------------------------
            */

            const updateMethod = function () {

                if (!method || !previewMethod) {
                    return;
                }

                const selected =
                    method.options[
                        method.selectedIndex
                    ];

                if (
                    selected &&
                    selected.value
                ) {

                    previewMethod.textContent =
                        selected.textContent.trim();

                } else {

                    previewMethod.textContent =
                        'غير محددة';
                }
            };

            /*
            |--------------------------------------------------------------------------
            | Notes counter
            |--------------------------------------------------------------------------
            */

            const updateNotesCounter = function () {

                if (!notes || !notesCounter) {
                    return;
                }

                const length =
                    notes.value.length;

                notesCounter.textContent =
                    length;

                notesCounter.classList.remove(
                    'text-danger',
                    'text-warning'
                );

                if (length > 1000) {

                    notesCounter.classList.add(
                        'text-danger'
                    );

                } else if (length > 900) {

                    notesCounter.classList.add(
                        'text-warning'
                    );
                }
            };

            /*
            |--------------------------------------------------------------------------
            | General checklist
            |--------------------------------------------------------------------------
            */

            const updateChecklist = function () {

                updateCheck(
                    checkClient,
                    client ? Boolean(client.value) : false
                );

                updateCheck(
                    checkEmployee,
                    employee ? Boolean(employee.value) : false
                );

                updateCheck(
                    checkAmount,
                    amount
                        ? Number(amount.value || 0) > 0
                        : false
                );

                updateCheck(
                    checkDate,
                    promiseDate
                        ? Boolean(promiseDate.value)
                        : false
                );
            };

            /*
            |--------------------------------------------------------------------------
            | Event listeners
            |--------------------------------------------------------------------------
            */

            if (client) {

                client.addEventListener(
                    'change',
                    updateChecklist
                );
            }

            if (employee) {

                employee.addEventListener(
                    'change',
                    updateChecklist
                );
            }

            if (amount) {

                amount.addEventListener(
                    'input',
                    function () {

                        updateAmount();
                        updateChecklist();

                    }
                );
            }

            if (promiseDate) {

                promiseDate.addEventListener(
                    'change',
                    function () {

                        updateDate();
                        updateChecklist();

                    }
                );
            }

            if (method) {

                method.addEventListener(
                    'change',
                    updateMethod
                );
            }

            if (notes) {

                notes.addEventListener(
                    'input',
                    updateNotesCounter
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Prevent old dates on frontend
            |--------------------------------------------------------------------------
            | Laravel must still validate this on the backend.
            |--------------------------------------------------------------------------
            */

            if (promiseDate) {

                promiseDate.min =
                    getToday();
            }

            /*
            |--------------------------------------------------------------------------
            | Initial state
            |--------------------------------------------------------------------------
            */

            updateAmount();
            updateDate();
            updateMethod();
            updateNotesCounter();
            updateChecklist();

            /*
            |--------------------------------------------------------------------------
            | Reset button
            |--------------------------------------------------------------------------
            */

            if (resetButton) {

                resetButton.addEventListener(
                    'click',
                    function () {

                        /*
                        | Keep CSRF token and reset all normal fields.
                        */
                        form.reset();

                        /*
                        | Restore tomorrow as the default date.
                        */
                        if (promiseDate) {

                            promiseDate.value =
                                @json($tomorrowDate);
                        }

                        updateAmount();
                        updateDate();
                        updateMethod();
                        updateNotesCounter();
                        updateChecklist();

                    }
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Prevent double submit
            |--------------------------------------------------------------------------
            */

            let submitted = false;

            form.addEventListener(
                'submit',
                function (event) {

                    /*
                    | Do not submit twice.
                    */
                    if (submitted) {

                        event.preventDefault();
                        return;
                    }

                    /*
                    | Basic frontend checks.
                    | Backend validation remains the final authority.
                    */
                    if (
                        client &&
                        !client.value
                    ) {

                        client.focus();
                        event.preventDefault();
                        return;
                    }

                    if (
                        employee &&
                        !employee.value
                    ) {

                        employee.focus();
                        event.preventDefault();
                        return;
                    }

                    if (
                        amount &&
                        Number(amount.value || 0) <= 0
                    ) {

                        amount.focus();
                        event.preventDefault();
                        return;
                    }

                    if (
                        promiseDate &&
                        !promiseDate.value
                    ) {

                        promiseDate.focus();
                        event.preventDefault();
                        return;
                    }

                    submitted = true;

                    if (submitButton) {

                        submitButton.disabled = true;

                        submitButton.classList.add(
                            'opacity-70',
                            'cursor-not-allowed'
                        );

                        submitButton.innerHTML =
                            '<i class="fa-solid fa-spinner fa-spin text-xs"></i> جاري تسجيل الوعد...';
                    }

                }
            );

            /*
            |--------------------------------------------------------------------------
            | Ctrl + Enter
            |--------------------------------------------------------------------------
            */

            form.addEventListener(
                'keydown',
                function (event) {

                    if (
                        event.ctrlKey &&
                        event.key === 'Enter'
                    ) {

                        event.preventDefault();

                        if (
                            typeof form.requestSubmit ===
                            'function'
                        ) {

                            form.requestSubmit();

                        } else {

                            form.submit();
                        }
                    }

                }
            );

            /*
            |--------------------------------------------------------------------------
            | Back to top
            |--------------------------------------------------------------------------
            */

            if (backToTop) {

                window.addEventListener(
                    'scroll',
                    function () {

                        if (window.scrollY > 500) {

                            backToTop.classList.remove(
                                'hidden'
                            );

                            backToTop.classList.add(
                                'flex'
                            );

                        } else {

                            backToTop.classList.add(
                                'hidden'
                            );

                            backToTop.classList.remove(
                                'flex'
                            );
                        }

                    },
                    {
                        passive: true
                    }
                );

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

        });
    </script>
@endpush