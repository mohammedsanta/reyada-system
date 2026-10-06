{{-- resources/views/banks/complaint.blade.php : one complaint --}}
@extends('layouts.app')

@section('title', 'شكوى ' . $complaint->reference)

@section('content')

    @php
        /*
        |--------------------------------------------------------------------------
        | Safe frontend helpers
        |--------------------------------------------------------------------------
        | These values already exist in the current page/backend.
        | Nothing new is required from the controller.
        */

        $sourceLabel = $sources[$complaint->source] ?? $complaint->source ?? 'غير محدد';
        $statusLabel = $statuses[$complaint->status] ?? $complaint->status ?? 'غير محدد';
        $priorityLabel = $priorities[$complaint->priority] ?? $complaint->priority ?? 'غير محدد';

        $isClosed = in_array($complaint->status, ['resolved', 'rejected']);

        $clientPhone = trim((string) ($complaint->client_phone ?? ''));
        $clientCode = trim((string) ($complaint->client_code ?? ''));
        $assignedName = trim((string) ($complaint->assigned_name ?? ''));

        $subjectText = trim((string) ($complaint->subject ?? ''));
        $descriptionText = trim((string) ($complaint->description ?? ''));
        $resolutionText = trim((string) ($complaint->resolution ?? ''));

        $descriptionLength = mb_strlen($descriptionText);
        $resolutionLength = mb_strlen($resolutionText);

        /*
        |--------------------------------------------------------------------------
        | Safe deadline indicator
        |--------------------------------------------------------------------------
        | This is only a visual indicator. The original due value is preserved.
        */
        $dueText = trim((string) ($complaint->due ?? ''));

        $dueTimestamp = null;

        if ($dueText !== '') {
            try {
                $dueTimestamp = \Carbon\Carbon::parse($dueText);
            } catch (\Throwable $e) {
                $dueTimestamp = null;
            }
        }

        $isOverdue = $dueTimestamp
            && !$isClosed
            && $dueTimestamp->isPast();

        $isDueToday = $dueTimestamp
            && !$isClosed
            && $dueTimestamp->isToday();

        $isDueSoon = $dueTimestamp
            && !$isClosed
            && !$isDueToday
            && $dueTimestamp->isFuture()
            && now()->diffInDays($dueTimestamp, false) <= 2;
    @endphp

    <x-bank-header
        :bank="$bank"
        :title="'شكوى ' . $complaint->reference"
        :subtitle="$complaint->subject"
        :back="route('banks.complaints.index', $bank->id)"
    />

    <x-flash />

    {{-- ================================================================
         QUICK ACTION BAR
         ================================================================ --}}
    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">

        <div class="flex flex-wrap items-center gap-2">

            {{-- Back --}}
            <a
                href="{{ route('banks.complaints.index', $bank->id) }}"
                class="btn btn-secondary btn-sm"
                title="العودة إلى الشكاوى"
            >
                <i class="fa-solid fa-arrow-right text-xs"></i>
                الشكاوى
            </a>

            {{-- Print --}}
            <button
                type="button"
                id="printComplaint"
                class="btn btn-secondary btn-sm"
                title="طباعة الشكوى"
            >
                <i class="fa-solid fa-print text-xs"></i>
                طباعة
            </button>

            {{-- Copy reference --}}
            <button
                type="button"
                id="copyReference"
                class="btn btn-secondary btn-sm"
                title="نسخ رقم الشكوى"
                data-copy="{{ $complaint->reference }}"
            >
                <i class="fa-regular fa-copy text-xs"></i>
                نسخ المرجع
            </button>

            {{-- Copy client code --}}
            @if($clientCode !== '')
                <button
                    type="button"
                    id="copyClientCode"
                    class="btn btn-secondary btn-sm"
                    title="نسخ كود العميل"
                    data-copy="{{ $clientCode }}"
                >
                    <i class="fa-regular fa-copy text-xs"></i>
                    كود العميل
                </button>
            @endif

            {{-- Call client --}}
            @if($clientPhone !== '')
                <a
                    href="tel:{{ $clientPhone }}"
                    class="btn btn-outline-success btn-sm"
                    title="الاتصال بالعميل"
                >
                    <i class="fa-solid fa-phone text-xs"></i>
                    اتصال
                </a>
            @endif

        </div>

        {{-- Current status --}}
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-[11px] text-dim">الحالة الحالية:</span>
            <x-status-badge type="complaint" :status="$complaint->status" />
        </div>

    </div>

    {{-- ================================================================
         MAIN CONTENT
         ================================================================ --}}
    <section class="grid grid-cols-1 gap-4 xl:grid-cols-3">

        <div class="space-y-4 xl:col-span-2">

            {{-- ========================================================
                 COMPLAINT DETAILS
                 ========================================================= --}}
            <x-section-card title="تفاصيل الشكوى" icon="fa-envelope-open-text" color="warning">

                {{-- Status / priority / source --}}
                <div class="mb-4 flex flex-wrap items-center gap-2">

                    <x-status-badge
                        type="complaint"
                        :status="$complaint->status"
                    />

                    <x-status-badge
                        type="priority"
                        :status="$complaint->priority"
                    />

                    <span class="badge badge-neutral">
                        {{ $sourceLabel }}
                    </span>

                    {{-- Deadline state --}}
                    @if($isOverdue)
                        <span class="badge badge-danger">
                            <i class="fa-solid fa-triangle-exclamation ml-1"></i>
                            متأخرة
                        </span>
                    @elseif($isDueToday)
                        <span class="badge badge-warning">
                            <i class="fa-solid fa-clock ml-1"></i>
                            مستحقة اليوم
                        </span>
                    @elseif($isDueSoon)
                        <span class="badge badge-info">
                            <i class="fa-solid fa-hourglass-half ml-1"></i>
                            موعدها قريب
                        </span>
                    @endif

                </div>

                {{-- Complaint reference --}}
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">

                    <div class="flex items-center gap-2">
                        <span class="text-[11px] text-dim">
                            رقم الشكوى
                        </span>

                        <span
                            id="complaintReference"
                            class="rounded-lg border border-white/10 bg-white/[0.03] px-2 py-1 font-mono text-xs text-fg"
                        >
                            {{ $complaint->reference }}
                        </span>
                    </div>

                    <span class="text-[11px] text-dim">
                        {{ $complaint->created }}
                    </span>

                </div>

                {{-- Subject --}}
                <h2 class="text-base font-bold text-fg">
                    {{ $complaint->subject }}
                </h2>

                {{-- Description --}}
                <div class="mt-3 rounded-xl border border-white/[0.06] bg-white/[0.02] p-4">

                    <div class="mb-2 flex items-center justify-between gap-2">
                        <span class="text-[11px] font-bold text-dim">
                            <i class="fa-solid fa-align-right ml-1"></i>
                            تفاصيل الشكوى
                        </span>

                        <span class="text-[10px] text-dim">
                            {{ number_format($descriptionLength) }} حرف
                        </span>
                    </div>

                    <p class="whitespace-pre-line text-sm leading-7 text-muted">
                        {{ $complaint->description }}
                    </p>

                </div>

                {{-- Complaint information summary --}}
                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">

                    <div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-3">
                        <p class="text-[10px] text-dim">المصدر</p>
                        <p class="mt-1 text-sm font-semibold text-fg">
                            {{ $sourceLabel }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-3">
                        <p class="text-[10px] text-dim">الأولوية</p>
                        <div class="mt-1">
                            <x-status-badge type="priority" :status="$complaint->priority" />
                        </div>
                    </div>

                    <div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-3">
                        <p class="text-[10px] text-dim">المسؤول</p>
                        <p class="mt-1 truncate text-sm font-semibold text-fg">
                            {{ $assignedName !== '' ? $assignedName : 'غير مسندة' }}
                        </p>
                    </div>

                </div>

                {{-- Resolution --}}
                @if($complaint->resolution)

                    <div class="mt-5 rounded-xl border border-brand/20 bg-brand/[0.04] p-4">

                        <div class="mb-2 flex items-center justify-between gap-2">

                            <p class="text-xs font-bold text-brand">
                                <i class="fa-solid fa-circle-check ml-1"></i>
                                نتيجة الشكوى
                            </p>

                            <span class="text-[10px] text-dim">
                                {{ number_format($resolutionLength) }} حرف
                            </span>

                        </div>

                        <p class="whitespace-pre-line text-sm leading-7 text-fg">
                            {{ $complaint->resolution }}
                        </p>

                    </div>

                @else

                    <div class="mt-5 rounded-xl border border-warning/20 bg-warning/[0.03] p-4">

                        <p class="text-xs font-bold text-warning">
                            <i class="fa-solid fa-circle-exclamation ml-1"></i>
                            لا توجد نتيجة مسجلة حتى الآن
                        </p>

                        <p class="mt-1 text-[11px] leading-6 text-dim">
                            يمكنك تسجيل نتيجة المعالجة من قسم "معالجة الشكوى".
                        </p>

                    </div>

                @endif

            </x-section-card>

            {{-- ========================================================
                 COMPLAINT TIMELINE
                 ========================================================= --}}
            <x-section-card title="سجل الشكوى" icon="fa-clock-rotate-left" color="cyan">

                <ol class="space-y-3 text-sm">

                    {{-- Created --}}
                    <li class="flex items-start gap-3">

                        <i class="fa-solid fa-circle mt-1.5 text-[7px] text-brand"></i>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="font-semibold text-fg">
                                    تم تسجيل الشكوى
                                </span>

                                <span class="text-[11px] text-dim">
                                    {{ $complaint->created }}
                                </span>
                            </div>

                            <p class="mt-1 text-[11px] text-dim">
                                تم إنشاء سجل الشكوى داخل النظام.
                            </p>
                        </div>

                    </li>

                    {{-- Assigned --}}
                    <li class="flex items-start gap-3">

                        <i class="fa-solid fa-circle mt-1.5 text-[7px] text-info"></i>

                        <div class="min-w-0 flex-1">

                            <div class="flex flex-wrap items-center justify-between gap-2">

                                <span class="font-semibold text-fg">
                                    أُسندت الشكوى
                                </span>

                                <span class="text-[11px] text-dim">
                                    {{ $complaint->created }}
                                </span>

                            </div>

                            <p class="mt-1 text-[11px] text-dim">
                                المسؤول:
                                <span class="text-fg">
                                    {{ $assignedName !== '' ? $assignedName : 'بدون إسناد' }}
                                </span>
                            </p>

                        </div>

                    </li>

                    {{-- Resolution --}}
                    @if($complaint->resolution)

                        <li class="flex items-start gap-3">

                            <i class="fa-solid fa-circle mt-1.5 text-[7px] text-brand"></i>

                            <div class="min-w-0 flex-1">

                                <div class="flex flex-wrap items-center justify-between gap-2">

                                    <span class="font-semibold text-fg">
                                        تم تسجيل نتيجة المعالجة
                                    </span>

                                </div>

                                <p class="mt-1 text-[11px] leading-6 text-dim">
                                    تم تسجيل نتيجة للشكوى.
                                </p>

                            </div>

                        </li>

                    @endif

                    {{-- Closed --}}
                    @if($complaint->status === 'resolved' || $complaint->status === 'rejected')

                        <li class="flex items-start gap-3">

                            <i class="fa-solid fa-circle mt-1.5 text-[7px] text-warning"></i>

                            <div class="min-w-0 flex-1">

                                <div class="flex flex-wrap items-center justify-between gap-2">

                                    <span class="font-semibold text-fg">
                                        تم إغلاق الشكوى
                                    </span>

                                    <x-status-badge
                                        type="complaint"
                                        :status="$complaint->status"
                                    />

                                </div>

                                <p class="mt-1 text-[11px] text-dim">
                                    الحالة النهائية:
                                    <span class="text-fg">
                                        {{ $statusLabel }}
                                    </span>
                                </p>

                            </div>

                        </li>

                    @endif

                </ol>

            </x-section-card>

        </div>

        {{-- ============================================================
             RIGHT COLUMN
             ============================================================ --}}
        <div class="space-y-4">

            {{-- ========================================================
                 CLIENT
                 ========================================================= --}}
            <x-section-card title="العميل" icon="fa-user" color="info">

                <div class="flex items-start justify-between gap-3">

                    <div class="min-w-0">

                        <p class="truncate text-sm font-bold text-fg">
                            {{ $complaint->client_name }}
                        </p>

                        <p class="mt-1 text-xs text-muted">
                            <span class="badge badge-info font-mono">
                                {{ $complaint->client_code }}
                            </span>
                        </p>

                    </div>

                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-info/20 bg-info/10 text-info">
                        <i class="fa-solid fa-user text-sm"></i>
                    </div>

                </div>

                {{-- Phone --}}
                <div class="mt-4 rounded-xl border border-white/[0.06] bg-white/[0.02] p-3">

                    <p class="text-[10px] text-dim">
                        رقم الهاتف
                    </p>

                    @if($clientPhone !== '')

                        <div class="mt-1 flex items-center justify-between gap-2">

                            <a
                                href="tel:{{ $clientPhone }}"
                                dir="ltr"
                                class="font-mono text-sm font-semibold text-fg transition hover:text-brand"
                            >
                                {{ $complaint->client_phone }}
                            </a>

                            <a
                                href="tel:{{ $clientPhone }}"
                                class="flex h-8 w-8 items-center justify-center rounded-lg border border-brand/20 bg-brand/10 text-brand transition hover:bg-brand/20"
                                title="الاتصال بالعميل"
                            >
                                <i class="fa-solid fa-phone text-xs"></i>
                            </a>

                        </div>

                    @else

                        <p class="mt-1 text-sm text-dim">
                            غير متوفر
                        </p>

                    @endif

                </div>

                {{-- Client quick information --}}
                <div class="mt-3 grid grid-cols-2 gap-2">

                    <div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-3">
                        <p class="text-[10px] text-dim">كود العميل</p>
                        <p class="mt-1 truncate font-mono text-xs font-semibold text-fg">
                            {{ $clientCode !== '' ? $clientCode : '—' }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-3">
                        <p class="text-[10px] text-dim">المسؤول</p>
                        <p class="mt-1 truncate text-xs font-semibold text-fg">
                            {{ $assignedName !== '' ? $assignedName : 'غير مسند' }}
                        </p>
                    </div>

                </div>

            </x-section-card>

            {{-- ========================================================
                 COMPLAINT PROCESSING
                 ========================================================= --}}
            <x-section-card title="معالجة الشكوى" icon="fa-pen-to-square" color="brand">

                <form
                    id="complaintForm"
                    method="POST"
                    action="{{ route('banks.complaints.update', [$bank->id, $complaint->id]) }}"
                    class="space-y-3"
                >

                    @csrf
                    @method('PUT')

                    <x-select-field
                        name="status"
                        label="الحالة"
                        :options="$statuses"
                        :value="$complaint->status"
                    />

                    <x-select-field
                        name="priority"
                        label="الأولوية"
                        :options="$priorities"
                        :value="$complaint->priority"
                    />

                    <x-select-field
                        name="assigned_to"
                        label="المسؤول"
                        :options="$employees"
                        :value="$complaint->assigned_id"
                        placeholder="بدون إسناد"
                    />

                    <x-textarea-field
                        name="resolution"
                        label="نتيجة الشكوى"
                        :value="$complaint->resolution"
                        rows="3"
                    />

                    {{-- Resolution helper --}}
                    <div
                        id="resolutionHint"
                        class="rounded-xl border border-info/20 bg-info/[0.04] p-3 text-[11px] leading-6 text-dim"
                    >
                        <i class="fa-solid fa-circle-info ml-1 text-info"></i>
                        عند إغلاق الشكوى، احرص على تسجيل نتيجة واضحة للمعالجة.
                    </div>

                    {{-- Save --}}
                    <button
                        id="saveComplaint"
                        type="submit"
                        class="btn btn-primary w-full"
                    >
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        حفظ التعديلات
                    </button>

                </form>

                {{-- Deadline --}}
                <div class="mt-3 rounded-xl border border-white/[0.06] bg-white/[0.02] p-3">

                    <div class="flex items-center justify-between gap-2">

                        <span class="text-[10px] text-dim">
                            <i class="fa-regular fa-calendar ml-1"></i>
                            الموعد النهائي للرد
                        </span>

                        @if($isOverdue)
                            <span class="text-[10px] font-bold text-danger">
                                متأخر
                            </span>
                        @elseif($isDueToday)
                            <span class="text-[10px] font-bold text-warning">
                                اليوم
                            </span>
                        @elseif($isDueSoon)
                            <span class="text-[10px] font-bold text-info">
                                قريب
                            </span>
                        @endif

                    </div>

                    <p class="mt-1 text-sm font-semibold text-fg">
                        {{ $complaint->due }}
                    </p>

                </div>

            </x-section-card>

            {{-- ========================================================
                 CASE SUMMARY
                 ========================================================= --}}
            <x-section-card title="ملخص الشكوى" icon="fa-chart-simple" color="cyan">

                <div class="space-y-3">

                    <div class="flex items-center justify-between gap-3">
                        <span class="text-xs text-dim">المرجع</span>
                        <span class="font-mono text-xs font-semibold text-fg">
                            {{ $complaint->reference }}
                        </span>
                    </div>

                    <div class="h-px bg-white/[0.05]"></div>

                    <div class="flex items-center justify-between gap-3">
                        <span class="text-xs text-dim">الحالة</span>
                        <x-status-badge
                            type="complaint"
                            :status="$complaint->status"
                        />
                    </div>

                    <div class="h-px bg-white/[0.05]"></div>

                    <div class="flex items-center justify-between gap-3">
                        <span class="text-xs text-dim">الأولوية</span>
                        <x-status-badge
                            type="priority"
                            :status="$complaint->priority"
                        />
                    </div>

                    <div class="h-px bg-white/[0.05]"></div>

                    <div class="flex items-center justify-between gap-3">
                        <span class="text-xs text-dim">المصدر</span>
                        <span class="text-xs font-semibold text-fg">
                            {{ $sourceLabel }}
                        </span>
                    </div>

                    <div class="h-px bg-white/[0.05]"></div>

                    <div class="flex items-center justify-between gap-3">
                        <span class="text-xs text-dim">المسؤول</span>
                        <span class="max-w-[55%] truncate text-xs font-semibold text-fg">
                            {{ $assignedName !== '' ? $assignedName : 'غير مسندة' }}
                        </span>
                    </div>

                    <div class="h-px bg-white/[0.05]"></div>

                    <div class="flex items-center justify-between gap-3">
                        <span class="text-xs text-dim">الحالة النهائية</span>

                        @if($isClosed)
                            <span class="text-xs font-bold text-brand">
                                <i class="fa-solid fa-circle-check ml-1"></i>
                                مغلقة
                            </span>
                        @else
                            <span class="text-xs font-bold text-warning">
                                <i class="fa-solid fa-hourglass-half ml-1"></i>
                                قيد المعالجة
                            </span>
                        @endif
                    </div>

                </div>

            </x-section-card>

        </div>

    </section>

    {{-- ================================================================
         BOTTOM STATUS BAR
         ================================================================ --}}
    <div class="mt-4 card">

        <div class="flex flex-wrap items-center justify-between gap-3">

            <div class="flex flex-wrap items-center gap-3">

                <div class="flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full {{ $isClosed ? 'bg-brand' : 'bg-warning' }}"></span>

                    <span class="text-xs font-semibold text-fg">
                        {{ $isClosed ? 'الشكوى مغلقة' : 'الشكوى قيد المتابعة' }}
                    </span>
                </div>

                <span class="hidden text-dim sm:inline">•</span>

                <span class="text-[11px] text-dim">
                    آخر حالة:
                    <span class="text-fg">{{ $statusLabel }}</span>
                </span>

            </div>

            <div class="flex items-center gap-2 text-[11px] text-dim">
                <i class="fa-solid fa-shield-halved"></i>
                سجل الشكوى محفوظ داخل النظام
            </div>

        </div>

    </div>

    {{-- ================================================================
         BACK TO TOP
         ================================================================ --}}
    <button
        type="button"
        id="backToTop"
        class="fixed bottom-5 left-5 z-40 hidden h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-[#111418] text-dim shadow-lg transition hover:text-brand"
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
    | Complaint page elements
    |--------------------------------------------------------------------------
    */

    const form = document.getElementById('complaintForm');
    const saveButton = document.getElementById('saveComplaint');
    const printButton = document.getElementById('printComplaint');
    const backToTop = document.getElementById('backToTop');

    const statusField = form
        ? form.querySelector('[name="status"]')
        : null;

    const resolutionField = form
        ? form.querySelector('[name="resolution"]')
        : null;

    let formChanged = false;
    let formSubmitting = false;

    /*
    |--------------------------------------------------------------------------
    | Copy helper
    |--------------------------------------------------------------------------
    */

    async function copyText(value, button) {

        if (!value) {
            return;
        }

        try {

            await navigator.clipboard.writeText(value);

            if (button) {

                const originalHTML = button.innerHTML;

                button.innerHTML =
                    '<i class="fa-solid fa-check text-xs"></i> تم النسخ';

                setTimeout(function () {
                    button.innerHTML = originalHTML;
                }, 1400);
            }

        } catch (error) {

            /*
             * Fallback for browsers where clipboard API is unavailable.
             */

            const textarea = document.createElement('textarea');

            textarea.value = value;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';

            document.body.appendChild(textarea);

            textarea.select();

            try {
                document.execCommand('copy');
            } catch (e) {
                // Nothing else to do.
            }

            textarea.remove();

            if (button) {

                const originalHTML = button.innerHTML;

                button.innerHTML =
                    '<i class="fa-solid fa-check text-xs"></i> تم النسخ';

                setTimeout(function () {
                    button.innerHTML = originalHTML;
                }, 1400);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Copy buttons
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('[data-copy]').forEach(function (button) {

        button.addEventListener('click', function () {
            copyText(this.dataset.copy || '', this);
        });

    });

    /*
    |--------------------------------------------------------------------------
    | Print
    |--------------------------------------------------------------------------
    */

    if (printButton) {

        printButton.addEventListener('click', function () {

            formChanged = false;

            window.print();

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Status helper
    |--------------------------------------------------------------------------
    */

    function updateResolutionHint() {

        const hint = document.getElementById('resolutionHint');

        if (!hint || !statusField) {
            return;
        }

        const status = statusField.value;

        if (status === 'resolved') {

            hint.innerHTML =
                '<i class="fa-solid fa-circle-check ml-1 text-brand"></i>' +
                'الشكوى سيتم اعتبارها محلولة. يفضل تسجيل نتيجة المعالجة بالتفصيل.';

            hint.className =
                'rounded-xl border border-brand/20 bg-brand/[0.04] p-3 text-[11px] leading-6 text-dim';

        } else if (status === 'rejected') {

            hint.innerHTML =
                '<i class="fa-solid fa-circle-xmark ml-1 text-danger"></i>' +
                'إذا كانت الشكوى مرفوضة، اكتب سبب الرفض أو نتيجة المعالجة بوضوح.';

            hint.className =
                'rounded-xl border border-danger/20 bg-danger/[0.04] p-3 text-[11px] leading-6 text-dim';

        } else {

            hint.innerHTML =
                '<i class="fa-solid fa-circle-info ml-1 text-info"></i>' +
                'عند إغلاق الشكوى، احرص على تسجيل نتيجة واضحة للمعالجة.';

            hint.className =
                'rounded-xl border border-info/20 bg-info/[0.04] p-3 text-[11px] leading-6 text-dim';
        }

    }

    if (statusField) {

        statusField.addEventListener('change', function () {

            formChanged = true;

            updateResolutionHint();

        });

        updateResolutionHint();
    }

    /*
    |--------------------------------------------------------------------------
    | Track form changes
    |--------------------------------------------------------------------------
    */

    if (form) {

        form.querySelectorAll('input, select, textarea').forEach(function (field) {

            field.addEventListener('change', function () {
                formChanged = true;
            });

            field.addEventListener('input', function () {
                formChanged = true;
            });

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Resolution character counter
    |--------------------------------------------------------------------------
    */

    if (resolutionField) {

        const counter = document.createElement('div');

        counter.className =
            'mt-1 text-left text-[10px] text-dim';

        resolutionField.parentElement.appendChild(counter);

        function updateCounter() {

            const length = resolutionField.value.length;

            counter.textContent =
                new Intl.NumberFormat('ar-EG').format(length) + ' حرف';

        }

        resolutionField.addEventListener('input', updateCounter);

        updateCounter();
    }

    /*
    |--------------------------------------------------------------------------
    | Submit protection
    |--------------------------------------------------------------------------
    */

    if (form) {

        form.addEventListener('submit', function (event) {

            if (formSubmitting) {

                event.preventDefault();

                return;
            }

            const selectedStatus = statusField
                ? statusField.value
                : '';

            /*
             * Ask for confirmation when closing/rejecting.
             */

            if (
                selectedStatus === 'resolved' ||
                selectedStatus === 'rejected'
            ) {

                const message = selectedStatus === 'resolved'
                    ? 'هل أنت متأكد من تسجيل الشكوى كمحلولة؟'
                    : 'هل أنت متأكد من رفض الشكوى؟';

                if (!window.confirm(message)) {

                    event.preventDefault();

                    return;
                }

            }

            formSubmitting = true;
            formChanged = false;

            if (saveButton) {

                saveButton.disabled = true;

                saveButton.classList.add('opacity-70', 'cursor-not-allowed');

                saveButton.innerHTML =
                    '<i class="fa-solid fa-spinner fa-spin text-xs"></i>' +
                    ' جاري الحفظ...';
            }

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Unsaved changes protection
    |--------------------------------------------------------------------------
    */

    window.addEventListener('beforeunload', function (event) {

        if (!formChanged || formSubmitting) {
            return;
        }

        event.preventDefault();

        event.returnValue = '';

    });

    /*
    |--------------------------------------------------------------------------
    | Keyboard shortcut
    |--------------------------------------------------------------------------
    | Ctrl + S => save
    */

    document.addEventListener('keydown', function (event) {

        if (
            (event.ctrlKey || event.metaKey) &&
            event.key.toLowerCase() === 's'
        ) {

            event.preventDefault();

            if (form && !formSubmitting) {
                form.requestSubmit();
            }

        }

    });

    /*
    |--------------------------------------------------------------------------
    | Back to top
    |--------------------------------------------------------------------------
    */

    function updateBackToTop() {

        if (!backToTop) {
            return;
        }

        if (window.scrollY > 350) {

            backToTop.classList.remove('hidden');
            backToTop.classList.add('flex');

        } else {

            backToTop.classList.add('hidden');
            backToTop.classList.remove('flex');

        }

    }

    window.addEventListener('scroll', updateBackToTop, {
        passive: true
    });

    updateBackToTop();

    if (backToTop) {

        backToTop.addEventListener('click', function () {

            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });

        });

    }

});
</script>
@endpush