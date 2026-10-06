{{-- resources/views/ptp/show.blade.php : one promise to pay --}}
@extends('layouts.app')

@section('title', 'وعد دفع #' . $promise->id)

@php
    $dueTone = [
        'muted'   => 'text-muted',
        'warning' => 'text-warning',
        'danger'  => 'text-danger',
        'brand'   => 'text-brand',
        'info'    => 'text-info',
    ];

    $barTone = [
        'warning' => 'bg-warning',
        'cyan'    => 'bg-cyan',
        'brand'   => 'bg-brand',
        'info'    => 'bg-info',
        'danger'  => 'bg-danger',
    ];

    /*
    |--------------------------------------------------------------------------
    | Safe display values
    |--------------------------------------------------------------------------
    | These do not change your backend structure.
    | They only protect the Blade page from missing/null values.
    |--------------------------------------------------------------------------
    */

    $promiseAmount = (float) ($promise->amount ?? 0);
    $paidAmount    = (float) ($promise->paid ?? 0);
    $remainingAmount = max(0, $promiseAmount - $paidAmount);

    $paidPercent = (float) ($promise->paid_percent ?? 0);
    $paidPercent = min(100, max(0, $paidPercent));

    $statusLabel = $statuses[$promise->status] ?? $promise->status;

    $clientPhone = $client->phone ?? null;
    $clientAddress = $client->address ?? null;

    /*
    |--------------------------------------------------------------------------
    | Small page calculations
    |--------------------------------------------------------------------------
    */

    $isCompleted = in_array(
        $promise->status,
        ['kept', 'partial'],
        true
    );

    $isBroken = $promise->status === 'broken';

    $hasRemaining = $remainingAmount > 0;

    $remainingTone = $hasRemaining
        ? 'text-danger'
        : 'text-brand';

    $progressTone = $barTone[$color] ?? 'bg-brand';
@endphp

@section('content')

    {{-- ================================================================ --}}
    {{-- PAGE HEADER --}}
    {{-- ================================================================ --}}

    <x-bank-header
        :bank="$bank"
        :title="'وعد دفع #' . $promise->id"
        :subtitle="$promise->client_name"
        :back="route('banks.ptp.index', $bank->id)"
    />

    <x-flash />

    {{-- ================================================================ --}}
    {{-- QUICK ACTIONS --}}
    {{-- ================================================================ --}}

    <section class="mb-4 flex flex-wrap items-center justify-between gap-3">

        <div class="flex flex-wrap items-center gap-2">

            {{-- Promise status --}}
            <x-status-badge
                type="ptp"
                :status="$promise->status"
            />

            {{-- Due state --}}
            <span class="badge {{ $dueTone[$promise->due_tone] ?? 'text-muted' }}">
                <i class="fa-solid fa-calendar-day ml-1 text-[10px]"></i>
                {{ $promise->due_label }}
            </span>

        </div>

        <div class="flex flex-wrap items-center gap-2">

            {{-- Print --}}
            <button
                type="button"
                onclick="window.print()"
                class="btn btn-secondary btn-sm"
                title="طباعة"
            >
                <i class="fa-solid fa-print text-xs"></i>
                طباعة
            </button>

            {{-- Copy promise ID --}}
            <button
                type="button"
                onclick="copyPromiseValue('promise-id', this)"
                class="btn btn-secondary btn-sm"
                title="نسخ رقم الوعد"
            >
                <i class="fa-regular fa-copy text-xs"></i>
                نسخ الرقم
            </button>

        </div>

    </section>

    {{-- ================================================================ --}}
    {{-- TOP SUMMARY --}}
    {{-- ================================================================ --}}

    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        {{-- Promise amount --}}
        <div class="card">
            <div class="flex items-center justify-between gap-3">

                <div>
                    <p class="text-[11px] text-dim">المبلغ الموعود</p>

                    <p class="mt-1 text-xl font-bold text-fg">
                        EGP {{ number_format($promiseAmount, 2) }}
                    </p>
                </div>

                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-warning/10 text-warning">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </span>

            </div>
        </div>

        {{-- Paid --}}
        <div class="card">
            <div class="flex items-center justify-between gap-3">

                <div>
                    <p class="text-[11px] text-dim">إجمالي المسدد</p>

                    <p class="mt-1 text-xl font-bold text-brand">
                        EGP {{ number_format($paidAmount, 2) }}
                    </p>
                </div>

                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand/10 text-brand">
                    <i class="fa-solid fa-circle-check"></i>
                </span>

            </div>
        </div>

        {{-- Remaining --}}
        <div class="card">
            <div class="flex items-center justify-between gap-3">

                <div>
                    <p class="text-[11px] text-dim">المبلغ المتبقي</p>

                    <p class="mt-1 text-xl font-bold {{ $remainingTone }}">
                        EGP {{ number_format($remainingAmount, 2) }}
                    </p>
                </div>

                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-danger/10 text-danger">
                    <i class="fa-solid fa-wallet"></i>
                </span>

            </div>
        </div>

        {{-- Progress --}}
        <div class="card">
            <div class="flex items-center justify-between gap-3">

                <div>
                    <p class="text-[11px] text-dim">نسبة السداد</p>

                    <p class="mt-1 text-xl font-bold text-fg">
                        {{ number_format($paidPercent, 0) }}%
                    </p>
                </div>

                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-info/10 text-info">
                    <i class="fa-solid fa-chart-pie"></i>
                </span>

            </div>

            <div class="mt-3 progress">
                <div
                    class="h-full rounded-full {{ $progressTone }}"
                    style="width: {{ $paidPercent }}%"
                ></div>
            </div>
        </div>

    </section>

    {{-- ================================================================ --}}
    {{-- IMPORTANT STATUS MESSAGE --}}
    {{-- ================================================================ --}}

    @if($isBroken)

        <div class="mb-4 rounded-xl border border-danger/20 bg-danger/5 p-4">
            <div class="flex items-start gap-3">

                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-danger/10 text-danger">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </span>

                <div>
                    <p class="text-sm font-bold text-fg">
                        هذا الوعد لم يتم الالتزام به
                    </p>

                    <p class="mt-1 text-xs leading-5 text-muted">
                        يمكنك مراجعة الملاحظات وتحديث حالة الوعد من نموذج التحديث الموجود بجانب التفاصيل.
                    </p>
                </div>

            </div>
        </div>

    @elseif($isCompleted && ! $hasRemaining)

        <div class="mb-4 rounded-xl border border-brand/20 bg-brand/5 p-4">
            <div class="flex items-start gap-3">

                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand/10 text-brand">
                    <i class="fa-solid fa-circle-check"></i>
                </span>

                <div>
                    <p class="text-sm font-bold text-fg">
                        تم سداد الوعد بالكامل
                    </p>

                    <p class="mt-1 text-xs leading-5 text-muted">
                        لا يوجد مبلغ متبقٍ على هذا الوعد.
                    </p>
                </div>

            </div>
        </div>

    @elseif($hasRemaining)

        <div class="mb-4 rounded-xl border border-warning/20 bg-warning/5 p-4">
            <div class="flex items-start gap-3">

                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-warning/10 text-warning">
                    <i class="fa-solid fa-hourglass-half"></i>
                </span>

                <div>
                    <p class="text-sm font-bold text-fg">
                        يوجد مبلغ متبقٍ على الوعد
                    </p>

                    <p class="mt-1 text-xs leading-5 text-muted">
                        المبلغ المتبقي:
                        <span class="font-bold text-warning">
                            EGP {{ number_format($remainingAmount, 2) }}
                        </span>
                    </p>
                </div>

            </div>
        </div>

    @endif

    {{-- ================================================================ --}}
    {{-- MAIN CONTENT --}}
    {{-- ================================================================ --}}

    <section class="grid grid-cols-1 gap-4 xl:grid-cols-3">

        {{-- ============================================================ --}}
        {{-- LEFT SIDE --}}
        {{-- ============================================================ --}}

        <div class="space-y-4 xl:col-span-2">

            {{-- ======================================================== --}}
            {{-- Promise --}}
            {{-- ======================================================== --}}

            <x-section-card
                title="تفاصيل الوعد"
                icon="fa-handshake"
                color="warning"
            >

                <div class="mb-5 flex flex-wrap items-center gap-3">

                    <x-status-badge
                        type="ptp"
                        :status="$promise->status"
                    />

                    <span class="text-sm font-semibold {{ $dueTone[$promise->due_tone] ?? 'text-muted' }}">
                        <i class="fa-solid fa-calendar-check ml-1 text-[10px]"></i>
                        {{ $promise->due_label }}
                    </span>

                    <span class="badge badge-info font-mono">
                        #{{ $promise->id }}
                    </span>

                </div>

                {{-- Financial information --}}
                <dl class="grid grid-cols-2 gap-5 text-sm md:grid-cols-4">

                    <div>
                        <dt class="text-[11px] text-dim">
                            المبلغ الموعود
                        </dt>

                        <dd class="mt-1 text-lg font-bold text-fg">
                            EGP {{ number_format($promiseAmount, 2) }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-[11px] text-dim">
                            المسدد
                        </dt>

                        <dd class="mt-1 text-lg font-bold text-brand">
                            EGP {{ number_format($paidAmount, 2) }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-[11px] text-dim">
                            المتبقي
                        </dt>

                        <dd class="mt-1 text-lg font-bold {{ $remainingTone }}">
                            EGP {{ number_format($remainingAmount, 2) }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-[11px] text-dim">
                            تاريخ السداد
                        </dt>

                        <dd class="mt-1 text-lg font-bold text-fg">
                            {{ $promise->promise_date_label }}
                        </dd>
                    </div>

                </dl>

                {{-- Payment progress --}}
                <div class="mt-5">

                    <div class="mb-1 flex justify-between text-[11px] text-muted">
                        <span>نسبة السداد</span>

                        <span class="font-semibold text-fg">
                            {{ number_format($paidPercent, 0) }}%
                        </span>
                    </div>

                    <div class="progress">
                        <div
                            class="h-full rounded-full {{ $progressTone }}"
                            style="width: {{ $paidPercent }}%"
                        ></div>
                    </div>

                </div>

                {{-- Detailed financial breakdown --}}
                <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">

                    <div class="rounded-xl border border-line bg-white/[0.02] p-3">
                        <p class="text-[10px] text-dim">قيمة الوعد</p>
                        <p class="mt-1 text-sm font-bold text-fg">
                            EGP {{ number_format($promiseAmount, 2) }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-line bg-white/[0.02] p-3">
                        <p class="text-[10px] text-dim">المسدد</p>
                        <p class="mt-1 text-sm font-bold text-brand">
                            EGP {{ number_format($paidAmount, 2) }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-line bg-white/[0.02] p-3">
                        <p class="text-[10px] text-dim">المتبقي</p>
                        <p class="mt-1 text-sm font-bold {{ $remainingTone }}">
                            EGP {{ number_format($remainingAmount, 2) }}
                        </p>
                    </div>

                </div>

                {{-- Notes --}}
                @if($promise->notes)

                    <div class="mt-5 rounded-xl border border-line bg-white/[0.02] p-4">

                        <div class="mb-2 flex items-center gap-2">
                            <i class="fa-solid fa-note-sticky text-xs text-dim"></i>

                            <p class="text-[11px] font-semibold text-dim">
                                ملاحظات
                            </p>
                        </div>

                        <p class="text-sm leading-6 text-fg">
                            {{ $promise->notes }}
                        </p>

                    </div>

                @endif

            </x-section-card>

            {{-- ======================================================== --}}
            {{-- Promise Metadata --}}
            {{-- ======================================================== --}}

            <x-section-card
                title="معلومات الوعد"
                icon="fa-circle-info"
                color="info"
            >

                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">

                    <div class="rounded-xl border border-line bg-white/[0.02] p-3">
                        <dt class="text-[10px] text-dim">
                            رقم الوعد
                        </dt>

                        <dd
                            id="promise-id"
                            class="mt-1 font-mono text-sm font-bold text-fg"
                        >
                            #{{ $promise->id }}
                        </dd>
                    </div>

                    <div class="rounded-xl border border-line bg-white/[0.02] p-3">
                        <dt class="text-[10px] text-dim">
                            الموظف المسؤول
                        </dt>

                        <dd class="mt-1 text-sm font-bold text-fg">
                            {{ $promise->employee }}
                        </dd>
                    </div>

                    <div class="rounded-xl border border-line bg-white/[0.02] p-3">
                        <dt class="text-[10px] text-dim">
                            الحالة الحالية
                        </dt>

                        <dd class="mt-1 text-sm font-bold text-fg">
                            {{ $statusLabel }}
                        </dd>
                    </div>

                    <div class="rounded-xl border border-line bg-white/[0.02] p-3">
                        <dt class="text-[10px] text-dim">
                            تاريخ إنشاء الوعد
                        </dt>

                        <dd class="mt-1 text-sm font-bold text-fg">
                            {{ $promise->created_at_label }}
                        </dd>
                    </div>

                    <div class="rounded-xl border border-line bg-white/[0.02] p-3">
                        <dt class="text-[10px] text-dim">
                            تاريخ الاستحقاق
                        </dt>

                        <dd class="mt-1 text-sm font-bold text-fg">
                            {{ $promise->promise_date_label }}
                        </dd>
                    </div>

                    <div class="rounded-xl border border-line bg-white/[0.02] p-3">
                        <dt class="text-[10px] text-dim">
                            البنك
                        </dt>

                        <dd class="mt-1 text-sm font-bold text-fg">
                            {{ $bank->name }}
                        </dd>
                    </div>

                </dl>

            </x-section-card>

            {{-- ======================================================== --}}
            {{-- Timeline --}}
            {{-- ======================================================== --}}

            <x-section-card
                title="سجل الوعد"
                icon="fa-clock-rotate-left"
                color="cyan"
            >

                <ol class="space-y-4 text-sm">

                    {{-- Created --}}
                    <li class="flex items-start gap-3">

                        <div class="mt-1 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-brand/10 text-brand">
                            <i class="fa-solid fa-plus text-[8px]"></i>
                        </div>

                        <div>
                            <p class="text-fg">
                                تم تسجيل الوعد بواسطة
                                <span class="font-semibold">
                                    {{ $promise->employee }}
                                </span>
                            </p>

                            <p class="mt-1 text-[11px] text-dim">
                                {{ $promise->created_at_label }}
                            </p>
                        </div>

                    </li>

                    {{-- Reviewed --}}
                    @if($promise->status !== 'active')

                        <li class="flex items-start gap-3">

                            <div class="mt-1 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-info/10 text-info">
                                <i class="fa-solid fa-eye text-[8px]"></i>
                            </div>

                            <div>
                                <p class="text-fg">
                                    تمت مراجعة الوعد
                                </p>

                                <p class="mt-1 text-[11px] text-dim">
                                    تم تحديث حالة الوعد
                                </p>
                            </div>

                        </li>

                    @endif

                    {{-- Closed --}}
                    @if(in_array($promise->status, ['kept', 'partial', 'broken'], true))

                        <li class="flex items-start gap-3">

                            <div class="mt-1 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-warning/10 text-warning">
                                <i class="fa-solid fa-flag-checkered text-[8px]"></i>
                            </div>

                            <div>
                                <p class="text-fg">
                                    أُغلق الوعد:
                                    <span class="font-semibold">
                                        {{ $statuses[$promise->status] }}
                                    </span>
                                </p>

                                <p class="mt-1 text-[11px] text-dim">
                                    الحالة الحالية للمتابعة
                                </p>
                            </div>

                        </li>

                    @endif

                </ol>

            </x-section-card>

        </div>

        {{-- ============================================================ --}}
        {{-- RIGHT SIDE --}}
        {{-- ============================================================ --}}

        <div class="space-y-4">

            {{-- ======================================================== --}}
            {{-- Client --}}
            {{-- ======================================================== --}}

            <x-section-card
                title="العميل"
                icon="fa-user"
                color="info"
            >

                <div class="flex items-start justify-between gap-3">

                    <div class="min-w-0">

                        <p class="truncate text-sm font-bold text-fg">
                            {{ $promise->client_name }}
                        </p>

                        <div class="mt-2 flex flex-wrap items-center gap-2">

                            <span
                                id="client-code"
                                class="badge badge-info font-mono"
                            >
                                {{ $promise->client_code }}
                            </span>

                        </div>

                    </div>

                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-info/10 text-info">
                        <i class="fa-solid fa-user"></i>
                    </span>

                </div>

                {{-- Phone --}}
                @if($clientPhone)

                    <div class="mt-4 flex items-center justify-between gap-3 rounded-xl border border-line bg-white/[0.02] p-3">

                        <div class="flex min-w-0 items-center gap-2">

                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand/10 text-brand">
                                <i class="fa-solid fa-phone text-xs"></i>
                            </span>

                            <a
                                href="tel:{{ $clientPhone }}"
                                dir="ltr"
                                class="truncate text-sm text-muted hover:text-fg"
                            >
                                {{ $clientPhone }}
                            </a>

                        </div>

                        <a
                            href="tel:{{ $clientPhone }}"
                            class="btn btn-secondary btn-sm"
                            title="اتصال"
                        >
                            <i class="fa-solid fa-phone text-xs"></i>
                        </a>

                    </div>

                @endif

                {{-- Address --}}
                @if($clientAddress)

                    <div class="mt-3 rounded-xl border border-line bg-white/[0.02] p-3">

                        <div class="mb-1 flex items-center gap-2">
                            <i class="fa-solid fa-location-dot text-xs text-dim"></i>

                            <span class="text-[10px] text-dim">
                                العنوان
                            </span>
                        </div>

                        <p class="text-xs leading-5 text-muted">
                            {{ $clientAddress }}
                        </p>

                    </div>

                @endif

                {{-- Employee --}}
                <div class="mt-3 flex items-center gap-2 text-xs text-muted">

                    <i class="fa-solid fa-user-tie text-dim"></i>

                    <span>
                        الموظف:
                        <strong class="text-fg">
                            {{ $promise->employee }}
                        </strong>
                    </span>

                </div>

            </x-section-card>

            {{-- ======================================================== --}}
            {{-- Update --}}
            {{-- ======================================================== --}}

            <x-section-card
                title="تحديث الوعد"
                icon="fa-pen-to-square"
                color="brand"
            >

                <form
                    id="promiseForm"
                    method="POST"
                    action="{{ route('banks.ptp.update', [$bank->id, $promise->id]) }}"
                    class="space-y-3"
                    data-amount="{{ $promiseAmount }}"
                    data-original-paid="{{ $paidAmount }}"
                >

                    @csrf
                    @method('PUT')

                    <x-select-field
                        name="status"
                        label="الحالة"
                        :options="$statuses"
                        :value="$promise->status"
                    />

                    <x-form-field
                        name="paid_amount"
                        label="المبلغ المسدد (EGP)"
                        type="number"
                        min="0"
                        max="{{ $promiseAmount }}"
                        step="0.01"
                        :value="$promise->paid"
                    />

                    {{-- Live payment preview --}}
                    <div
                        id="paymentPreview"
                        class="rounded-xl border border-line bg-white/[0.02] p-3"
                    >

                        <div class="flex items-center justify-between gap-3 text-xs">

                            <span class="text-dim">
                                المتبقي بعد التحديث
                            </span>

                            <strong
                                id="liveRemaining"
                                class="{{ $remainingTone }}"
                            >
                                EGP {{ number_format($remainingAmount, 2) }}
                            </strong>

                        </div>

                        <div class="mt-2 progress">
                            <div
                                id="liveProgress"
                                class="h-full rounded-full {{ $progressTone }}"
                                style="width: {{ $paidPercent }}%"
                            ></div>
                        </div>

                        <div class="mt-1 text-left text-[10px] text-dim">
                            <span id="livePercent">
                                {{ number_format($paidPercent, 0) }}%
                            </span>
                        </div>

                    </div>

                    <x-textarea-field
                        name="notes"
                        label="ملاحظات"
                        :value="$promise->notes"
                        rows="3"
                    />

                    <button
                        id="savePromiseButton"
                        type="submit"
                        class="btn btn-primary w-full"
                    >
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        حفظ
                    </button>

                </form>

            </x-section-card>

            {{-- ======================================================== --}}
            {{-- Payment Summary --}}
            {{-- ======================================================== --}}

            <x-section-card
                title="ملخص مالي"
                icon="fa-chart-simple"
                color="brand"
            >

                <div class="space-y-3">

                    <div class="flex items-center justify-between text-sm">
                        <span class="text-muted">
                            إجمالي الوعد
                        </span>

                        <span class="font-bold text-fg">
                            EGP {{ number_format($promiseAmount, 2) }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <span class="text-muted">
                            المدفوع
                        </span>

                        <span class="font-bold text-brand">
                            EGP {{ number_format($paidAmount, 2) }}
                        </span>
                    </div>

                    <div class="h-px bg-line"></div>

                    <div class="flex items-center justify-between text-sm">
                        <span class="font-semibold text-fg">
                            المتبقي
                        </span>

                        <span class="font-bold {{ $remainingTone }}">
                            EGP {{ number_format($remainingAmount, 2) }}
                        </span>
                    </div>

                </div>

            </x-section-card>

        </div>

    </section>

    {{-- ================================================================ --}}
    {{-- MOBILE / BOTTOM ACTION BAR --}}
    {{-- ================================================================ --}}

    <section class="mt-4 card">

        <div class="flex flex-wrap items-center justify-between gap-3">

            <div>
                <p class="text-sm font-bold text-fg">
                    وعد الدفع #{{ $promise->id }}
                </p>

                <p class="mt-1 text-xs text-muted">
                    {{ $promise->client_name }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">

                <a
                    href="{{ route('banks.ptp.index', $bank->id) }}"
                    class="btn btn-secondary btn-sm"
                >
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                    العودة للوعود
                </a>

                <button
                    type="button"
                    onclick="document.getElementById('promiseForm')?.scrollIntoView({ behavior: 'smooth', block: 'center' })"
                    class="btn btn-primary btn-sm"
                >
                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                    تحديث الوعد
                </button>

            </div>

        </div>

    </section>

    {{-- ================================================================ --}}
    {{-- BACK TO TOP --}}
    {{-- ================================================================ --}}

    <button
        id="ptpBackToTop"
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
            | Elements
            |--------------------------------------------------------------------------
            */

            const form = document.getElementById('promiseForm');
            const status = document.getElementById('status');
            const paid = document.getElementById('paid_amount');

            const liveRemaining = document.getElementById('liveRemaining');
            const liveProgress = document.getElementById('liveProgress');
            const livePercent = document.getElementById('livePercent');

            const saveButton = document.getElementById('savePromiseButton');

            const backToTop = document.getElementById('ptpBackToTop');

            /*
            |--------------------------------------------------------------------------
            | Promise amount
            |--------------------------------------------------------------------------
            */

            const promiseAmount = form
                ? Number(form.dataset.amount || 0)
                : 0;

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
            | Update payment preview
            |--------------------------------------------------------------------------
            */

            const updatePaymentPreview = function () {

                if (!paid) {
                    return;
                }

                let paidValue = Number(paid.value || 0);

                /*
                | Never allow negative values.
                */
                if (paidValue < 0) {
                    paidValue = 0;
                    paid.value = 0;
                }

                /*
                | Never allow payment above the promise amount.
                */
                if (paidValue > promiseAmount) {
                    paidValue = promiseAmount;
                    paid.value = promiseAmount;
                }

                const remaining = Math.max(
                    0,
                    promiseAmount - paidValue
                );

                const percentage = promiseAmount > 0
                    ? Math.min(
                        100,
                        Math.max(
                            0,
                            (paidValue / promiseAmount) * 100
                        )
                    )
                    : 0;

                if (liveRemaining) {
                    liveRemaining.textContent =
                        'EGP ' + formatMoney(remaining);

                    liveRemaining.classList.remove(
                        'text-danger',
                        'text-brand'
                    );

                    liveRemaining.classList.add(
                        remaining > 0
                            ? 'text-danger'
                            : 'text-brand'
                    );
                }

                if (liveProgress) {
                    liveProgress.style.width = percentage + '%';
                }

                if (livePercent) {
                    livePercent.textContent =
                        Math.round(percentage) + '%';
                }
            };

            /*
            |--------------------------------------------------------------------------
            | Status behavior
            |--------------------------------------------------------------------------
            */

            if (status && paid && form) {

                status.addEventListener('change', function () {

                    /*
                    | Kept = full payment.
                    */
                    if (status.value === 'kept') {
                        paid.value = promiseAmount;
                    }

                    /*
                    | Active / review = no payment by default.
                    */
                    if (
                        status.value === 'active' ||
                        status.value === 'review'
                    ) {
                        paid.value = 0;
                    }

                    updatePaymentPreview();
                });
            }

            /*
            |--------------------------------------------------------------------------
            | Payment input
            |--------------------------------------------------------------------------
            */

            if (paid) {
                paid.addEventListener(
                    'input',
                    updatePaymentPreview
                );

                paid.addEventListener(
                    'change',
                    updatePaymentPreview
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Initial calculation
            |--------------------------------------------------------------------------
            */

            updatePaymentPreview();

            /*
            |--------------------------------------------------------------------------
            | Form submit protection
            |--------------------------------------------------------------------------
            */

            if (form) {

                form.addEventListener('submit', function () {

                    if (paid) {

                        let value = Number(
                            paid.value || 0
                        );

                        if (value < 0) {
                            paid.value = 0;
                        }

                        if (value > promiseAmount) {
                            paid.value = promiseAmount;
                        }
                    }

                    if (saveButton) {

                        saveButton.disabled = true;

                        saveButton.classList.add(
                            'opacity-70',
                            'cursor-not-allowed'
                        );

                        saveButton.innerHTML =
                            '<i class="fa-solid fa-spinner fa-spin text-xs"></i> جاري الحفظ...';
                    }
                });
            }

            /*
            |--------------------------------------------------------------------------
            | Back to top
            |--------------------------------------------------------------------------
            */

            if (backToTop) {

                window.addEventListener('scroll', function () {

                    if (window.scrollY > 500) {

                        backToTop.classList.remove('hidden');
                        backToTop.classList.add('flex');

                    } else {

                        backToTop.classList.add('hidden');
                        backToTop.classList.remove('flex');

                    }

                }, { passive: true });

                backToTop.addEventListener('click', function () {

                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });

                });
            }

        });

        /*
        |--------------------------------------------------------------------------
        | Copy helper
        |--------------------------------------------------------------------------
        */

        function copyPromiseValue(elementId, button) {

            const element = document.getElementById(elementId);

            if (!element) {
                return;
            }

            const value = element.textContent.trim();

            if (!navigator.clipboard) {
                return;
            }

            navigator.clipboard.writeText(value)
                .then(function () {

                    const original = button.innerHTML;

                    button.innerHTML =
                        '<i class="fa-solid fa-check text-xs"></i> تم النسخ';

                    setTimeout(function () {
                        button.innerHTML = original;
                    }, 1500);

                })
                .catch(function () {
                    // Clipboard can be unavailable in some browser contexts.
                });
        }
    </script>
@endpush