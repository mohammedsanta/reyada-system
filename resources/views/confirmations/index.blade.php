{{-- resources/views/confirmations/index.blade.php --}}
@extends('layouts.app')

@section('title', 'تأكيد التحصيلات')

@php
    /*
    |--------------------------------------------------------------------------
    | Safe helpers
    |--------------------------------------------------------------------------
    |
    | لا نفترض أن $payments دائمًا Paginator.
    | الصفحة تعمل سواء كانت Collection أو LengthAwarePaginator.
    |
    */

    $isPaginator = method_exists($payments, 'total');

    $totalPayments = $isPaginator
        ? $payments->total()
        : $payments->count();

    $visiblePayments = collect($payments);

    /*
    |--------------------------------------------------------------------------
    | Safe calculated statistics
    |--------------------------------------------------------------------------
    */

    $visibleAmount = $visiblePayments->sum(function ($payment) {
        return (float) ($payment->amount ?? 0);
    });

    $visiblePending = $visiblePayments->where('status', 'pending')->count();
    $visibleConfirmed = $visiblePayments->where('status', 'confirmed')->count();
    $visibleRejected = $visiblePayments->where('status', 'rejected')->count();

    /*
    |--------------------------------------------------------------------------
    | Payment methods summary
    |--------------------------------------------------------------------------
    */

    $methodSummary = $visiblePayments
        ->groupBy('method')
        ->map(fn ($items) => $items->count());

    /*
    |--------------------------------------------------------------------------
    | Collector summary
    |--------------------------------------------------------------------------
    */

    $collectorSummary = $visiblePayments
        ->groupBy('collector')
        ->map(fn ($items) => $items->count())
        ->sortDesc();

    /*
    |--------------------------------------------------------------------------
    | Bank summary
    |--------------------------------------------------------------------------
    */

    $bankSummary = $visiblePayments
        ->groupBy('bank')
        ->map(fn ($items) => $items->count())
        ->sortDesc();

    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    $filterSearch = (string) request('search', '');
    $filterMethod = (string) request('method', '');
    $filterBank = (string) request('bank', '');
    $filterCollector = (string) request('collector', '');

    /*
    |--------------------------------------------------------------------------
    | Existing values from the current page
    |--------------------------------------------------------------------------
    */

    $availableMethods = $visiblePayments
        ->pluck('method')
        ->filter(fn ($value) => filled($value))
        ->unique()
        ->values();

    $availableBanks = $visiblePayments
        ->pluck('bank')
        ->filter(fn ($value) => filled($value))
        ->unique()
        ->values();

    $availableCollectors = $visiblePayments
        ->pluck('collector')
        ->filter(fn ($value) => filled($value))
        ->unique()
        ->values();

    /*
    |--------------------------------------------------------------------------
    | Safe percentage
    |--------------------------------------------------------------------------
    */

    $pendingPercentage = $totalPayments > 0
        ? round(($visiblePending / max(1, $visiblePayments->count())) * 100)
        : 0;
@endphp

@section('content')

    {{-- ================================================================
         HEADER
         ================================================================= --}}

    <x-page-header
        title="تأكيد التحصيلات"
        subtitle="مراجعة إيصالات السداد واعتمادها"
        icon="fa-circle-check">

        <x-slot:actions>

            {{-- Refresh --}}
            <a
                href="{{ url()->current() }}"
                class="btn btn-secondary btn-sm"
                title="تحديث البيانات">

                <i class="fa-solid fa-rotate-right text-xs"></i>

                تحديث
            </a>

        </x-slot:actions>

    </x-page-header>

    <x-flash />

    {{-- ================================================================
         TOP STATISTICS
         ================================================================= --}}

    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">

        <x-metric-card
            label="بانتظار التأكيد"
            :value="$counts['pending']"
            icon="fa-hourglass-half"
            color="warning" />

        <x-metric-card
            label="مبلغ بانتظار التأكيد"
            :value="number_format($pendingAmount)"
            unit="EGP"
            icon="fa-money-bill-wave"
            color="orange" />

        <x-metric-card
            label="مبلغ تم تأكيده"
            :value="number_format($confirmedAmount)"
            unit="EGP"
            icon="fa-circle-check"
            color="brand" />

    </section>


    {{-- ================================================================
         SECONDARY OVERVIEW
         ================================================================= --}}

    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <x-stat-card
            label="إجمالي الإيصالات"
            :value="$totalPayments"
            unit="إيصال" />

        <x-stat-card
            label="المعروض حاليًا"
            :value="$visiblePayments->count()"
            unit="إيصال" />

        <x-stat-card
            label="إجمالي المبالغ المعروضة"
            :value="number_format($visibleAmount, 2)"
            unit="EGP" />

        <x-stat-card
            label="نسبة الانتظار"
            :value="$pendingPercentage"
            unit="%" />

    </section>


    {{-- ================================================================
         STATUS NAVIGATION
         ================================================================= --}}

    <div class="segmented mb-6">

        @foreach([
            'pending' => 'بانتظار التأكيد',
            'confirmed' => 'مؤكدة',
            'rejected' => 'مرفوضة'
        ] as $key => $label)

            <a
                href="{{ route('confirmations.index', ['status' => $key]) }}"
                class="segmented-item {{ $status === $key ? 'segmented-item-active' : '' }}">

                {{ $label }}

                <span class="text-[10px] opacity-70">
                    ({{ $counts[$key] }})
                </span>

            </a>

        @endforeach

    </div>


    {{-- ================================================================
         FILTERS
         ================================================================= --}}

    <form
        id="confirmationFilters"
        method="GET"
        action="{{ route('confirmations.index') }}"
        class="card filter-bar mb-4">

        {{-- Preserve status --}}
        <input
            type="hidden"
            name="status"
            value="{{ $status }}">

        {{-- Search --}}
        <div class="min-w-[240px] flex-1">

            <label
                for="confirmation-search"
                class="sr-only">
                البحث
            </label>

            <div class="relative">

                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-dim"></i>

                <input
                    id="confirmation-search"
                    name="search"
                    type="search"
                    value="{{ $filterSearch }}"
                    autocomplete="off"
                    placeholder="ابحث برقم الإيصال، العميل أو كود الحالة..."
                    class="form-input pl-9">

            </div>

        </div>


        {{-- Payment method --}}
        <div class="w-40">

            <label
                for="confirmation-method"
                class="form-label">
                طريقة الدفع
            </label>

            <select
                id="confirmation-method"
                name="method"
                class="form-input">

                <option value="">
                    الكل
                </option>

                @foreach($availableMethods as $method)

                    <option
                        value="{{ $method }}"
                        @selected($filterMethod === (string) $method)>

                        {{ $methods[$method] ?? $method }}

                    </option>

                @endforeach

            </select>

        </div>


        {{-- Bank --}}
        <div class="w-40">

            <label
                for="confirmation-bank"
                class="form-label">
                البنك
            </label>

            <select
                id="confirmation-bank"
                name="bank"
                class="form-input">

                <option value="">
                    الكل
                </option>

                @foreach($availableBanks as $bankName)

                    <option
                        value="{{ $bankName }}"
                        @selected($filterBank === (string) $bankName)>

                        {{ $bankName }}

                    </option>

                @endforeach

            </select>

        </div>


        {{-- Collector --}}
        <div class="w-40">

            <label
                for="confirmation-collector"
                class="form-label">
                المحصل
            </label>

            <select
                id="confirmation-collector"
                name="collector"
                class="form-input">

                <option value="">
                    الكل
                </option>

                @foreach($availableCollectors as $collector)

                    <option
                        value="{{ $collector }}"
                        @selected($filterCollector === (string) $collector)>

                        {{ $collector }}

                    </option>

                @endforeach

            </select>

        </div>


        {{-- Apply --}}
        <button
            type="submit"
            class="btn btn-primary">

            <i class="fa-solid fa-filter text-xs"></i>

            فلترة

        </button>


        {{-- Reset --}}
        <a
            href="{{ route('confirmations.index', ['status' => $status]) }}"
            class="btn btn-secondary"
            title="إعادة ضبط الفلاتر">

            <i class="fa-solid fa-rotate-right"></i>

        </a>

    </form>


    {{-- ================================================================
         ACTIVE FILTERS
         ================================================================= --}}

    @if(
        filled($filterSearch) ||
        filled($filterMethod) ||
        filled($filterBank) ||
        filled($filterCollector)
    )

        <div class="mb-4 flex flex-wrap items-center gap-2">

            <span class="text-xs text-muted">
                الفلاتر الحالية:
            </span>

            @if(filled($filterSearch))

                <span class="badge badge-outline-info">
                    البحث:
                    {{ $filterSearch }}
                </span>

            @endif

            @if(filled($filterMethod))

                <span class="badge badge-outline-info">
                    الدفع:
                    {{ $methods[$filterMethod] ?? $filterMethod }}
                </span>

            @endif

            @if(filled($filterBank))

                <span class="badge badge-outline-info">
                    البنك:
                    {{ $filterBank }}
                </span>

            @endif

            @if(filled($filterCollector))

                <span class="badge badge-outline-info">
                    المحصل:
                    {{ $filterCollector }}
                </span>

            @endif

        </div>

    @endif


    {{-- ================================================================
         BULK TOOLBAR
         ================================================================= --}}

    <div
        id="confirmationToolbar"
        class="mb-3 flex flex-wrap items-center gap-2">

        {{-- Select all --}}
        <label
            class="flex cursor-pointer items-center gap-2 rounded-lg border border-line bg-surface px-3 py-2 text-xs text-muted">

            <input
                id="confirmation-check-all"
                type="checkbox"
                class="accent-brand"
                aria-label="تحديد كل الإيصالات">

            تحديد الكل

        </label>


        {{-- Copy receipt --}}
        <button
            type="button"
            class="icon-btn icon-btn-info"
            data-copy-selected-receipts
            data-needs-selection
            disabled
            title="نسخ أرقام الإيصالات">

            <i class="fa-solid fa-copy"></i>

        </button>


        {{-- Selection count --}}
        <span class="mr-2 text-xs text-muted">

            المحدد:

            <b
                id="confirmation-selected-count"
                class="text-fg">
                0
            </b>

        </span>


        {{-- Selection amount --}}
        <span
            id="confirmation-selected-amount-wrapper"
            class="hidden text-xs text-muted">

            المبلغ:

            <b
                id="confirmation-selected-amount"
                class="text-brand">
                0.00
            </b>

            EGP

        </span>

    </div>


    {{-- ================================================================
         TABLE
         ================================================================= --}}

    <div class="table-wrap">

        <table class="data-table whitespace-nowrap">

            <thead>

                <tr>

                    <th class="w-10">

                        <input
                            id="confirmation-check-all-header"
                            type="checkbox"
                            class="accent-brand"
                            aria-label="تحديد الكل">

                    </th>

                    <th>
                        رقم الإيصال
                    </th>

                    <th>
                        العميل
                    </th>

                    <th>
                        البنك
                    </th>

                    <th>
                        المحصل
                    </th>

                    <th>
                        المبلغ
                    </th>

                    <th>
                        طريقة الدفع
                    </th>

                    <th>
                        وقت السداد
                    </th>

                    <th>
                        الحالة
                    </th>

                    <th>
                        الإجراءات
                    </th>

                </tr>

            </thead>


            <tbody>

                @forelse($payments as $payment)

                    <tr>

                        {{-- ==================================================
                             Selection
                             ================================================== --}}

                        <td>

                            <input
                                type="checkbox"
                                class="confirmation-row-check accent-brand"
                                value="{{ $payment->id }}"
                                data-receipt="{{ $payment->receipt }}"
                                data-amount="{{ $payment->amount }}"
                                aria-label="تحديد الإيصال {{ $payment->receipt }}">

                        </td>


                        {{-- ==================================================
                             Receipt
                             ================================================== --}}

                        <td>

                            <div class="flex items-center gap-2">

                                <span
                                    class="badge badge-info font-mono">

                                    {{ $payment->receipt }}

                                </span>

                                <button
                                    type="button"
                                    class="icon-btn icon-btn-info"
                                    data-copy-value="{{ $payment->receipt }}"
                                    title="نسخ رقم الإيصال"
                                    aria-label="نسخ رقم الإيصال">

                                    <i class="fa-solid fa-copy text-[10px]"></i>

                                </button>

                            </div>

                        </td>


                        {{-- ==================================================
                             Client
                             ================================================== --}}

                        <td>

                            <div>

                                <p class="font-semibold text-fg">
                                    {{ $payment->client }}
                                </p>

                                <button
                                    type="button"
                                    class="mt-0.5 font-mono text-[11px] text-dim transition hover:text-brand"
                                    data-copy-value="{{ $payment->case_code }}"
                                    title="نسخ كود الحالة">

                                    {{ $payment->case_code }}

                                </button>

                            </div>

                        </td>


                        {{-- ==================================================
                             Bank
                             ================================================== --}}

                        <td>
                            {{ $payment->bank }}
                        </td>


                        {{-- ==================================================
                             Collector
                             ================================================== --}}

                        <td>

                            <span class="font-medium text-fg">
                                {{ $payment->collector }}
                            </span>

                        </td>


                        {{-- ==================================================
                             Amount
                             ================================================== --}}

                        <td>

                            <span class="font-bold text-brand">
                                EGP {{ number_format($payment->amount, 2) }}
                            </span>

                        </td>


                        {{-- ==================================================
                             Method
                             ================================================== --}}

                        <td>

                            <span class="badge badge-neutral">

                                {{ $methods[$payment->method] ?? $payment->method }}

                            </span>

                        </td>


                        {{-- ==================================================
                             Paid at
                             ================================================== --}}

                        <td>

                            <span
                                class="text-xs text-muted"
                                dir="ltr">

                                {{ $payment->paid_at }}

                            </span>

                        </td>


                        {{-- ==================================================
                             Status
                             ================================================== --}}

                        <td>

                            <x-status-badge
                                type="payment"
                                :status="$payment->status" />

                        </td>


                        {{-- ==================================================
                             Actions
                             ================================================== --}}

                        <td>

                            @if($payment->status === 'pending')

                                <div class="flex items-center gap-2">

                                    {{-- Approve --}}
                                    <form
                                        method="POST"
                                        action="{{ route('confirmations.approve', $payment->id) }}"
                                        data-approve-form>

                                        @csrf

                                        <button
                                            type="submit"
                                            class="btn btn-outline-success btn-sm"
                                            data-approve-button>

                                            <i class="fa-solid fa-check text-xs"></i>

                                            <span>
                                                تأكيد
                                            </span>

                                        </button>

                                    </form>


                                    {{-- Reject --}}
                                    <button
                                        type="button"
                                        class="btn btn-danger btn-sm"
                                        data-reject="{{ $payment->id }}"
                                        data-receipt="{{ $payment->receipt }}">

                                        <i class="fa-solid fa-xmark text-xs"></i>

                                        رفض

                                    </button>

                                </div>

                            @else

                                <span class="text-dim">
                                    -
                                </span>

                            @endif

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="10">

                            <x-empty-state
                                text="لا توجد تحصيلات في هذه الحالة" />

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>


    {{-- ================================================================
         PAGINATION / TABLE FOOTER
         ================================================================= --}}

    <div class="mt-4 flex flex-wrap items-center justify-between gap-4 text-xs text-muted">

        <div>

            @if($isPaginator && $payments->total() > 0)

                عرض
                {{ $payments->firstItem() }}
                -
                {{ $payments->lastItem() }}
                من
                {{ $payments->total() }}

            @elseif($visiblePayments->count() > 0)

                عرض
                {{ $visiblePayments->count() }}
                إيصال

            @else

                لا توجد نتائج

            @endif

        </div>


        @if($isPaginator)

            <div class="flex items-center gap-2">

                @if($payments->previousPageUrl())

                    <a
                        href="{{ $payments->previousPageUrl() }}"
                        class="btn btn-secondary btn-sm">

                        <i class="fa-solid fa-chevron-right text-[10px]"></i>

                        السابق

                    </a>

                @else

                    <span
                        class="btn btn-secondary btn-sm"
                        aria-disabled="true"
                        style="opacity:.4;pointer-events:none">

                        <i class="fa-solid fa-chevron-right text-[10px]"></i>

                        السابق

                    </span>

                @endif


                <span>

                    صفحة

                    <b class="text-fg">
                        {{ $payments->currentPage() }}
                    </b>

                    /

                    {{ max(1, $payments->lastPage()) }}

                </span>


                @if($payments->nextPageUrl())

                    <a
                        href="{{ $payments->nextPageUrl() }}"
                        class="btn btn-secondary btn-sm">

                        التالي

                        <i class="fa-solid fa-chevron-left text-[10px]"></i>

                    </a>

                @else

                    <span
                        class="btn btn-secondary btn-sm"
                        aria-disabled="true"
                        style="opacity:.4;pointer-events:none">

                        التالي

                        <i class="fa-solid fa-chevron-left text-[10px]"></i>

                    </span>

                @endif

            </div>

        @endif

    </div>


    {{-- ================================================================
         REJECT MODAL
         One reject dialog reused for every row.
         ================================================================= --}}

    <x-modal
        id="rejectModal"
        title="رفض التحصيل"
        width="30rem">

        <form
            id="rejectForm"
            method="POST"
            action="#"
            class="space-y-4">

            @csrf

            <input
                type="hidden"
                name="_reject_id"
                id="reject-id"
                value="">


            <div class="rounded-xl border border-danger/20 bg-danger/[0.05] p-3">

                <div class="flex items-start gap-3">

                    <span
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-danger/10 text-danger">

                        <i class="fa-solid fa-triangle-exclamation"></i>

                    </span>

                    <div>

                        <p class="text-sm font-semibold text-fg">
                            رفض التحصيل
                        </p>

                        <p class="mt-1 text-xs text-muted">

                            سيتم رفض الإيصال

                            <b
                                id="reject-receipt"
                                class="font-mono text-fg">
                            </b>

                        </p>

                    </div>

                </div>

            </div>


            <x-textarea-field
                name="reason"
                label="سبب الرفض"
                rows="3"
                required />

            <p
                id="reject-reason-counter"
                class="text-[10px] text-dim">
                0 / 255
            </p>


            <div class="flex gap-2">

                <button
                    id="reject-submit"
                    type="submit"
                    class="btn btn-danger">

                    <i class="fa-solid fa-xmark text-xs"></i>

                    <span>
                        تأكيد الرفض
                    </span>

                </button>


                <button
                    type="button"
                    class="btn btn-secondary"
                    data-close-modal>

                    إلغاء

                </button>

            </div>

        </form>

    </x-modal>

@endsection


@push('scripts')

<script>

(function () {

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    */

    const rejectUrl =
        @json(route('confirmations.reject', '__ID__'));


    /*
    |--------------------------------------------------------------------------
    | Elements
    |--------------------------------------------------------------------------
    */

    const rejectModal =
        document.getElementById('rejectModal');

    const rejectForm =
        document.getElementById('rejectForm');

    const rejectId =
        document.getElementById('reject-id');

    const rejectReceipt =
        document.getElementById('reject-receipt');

    const rejectSubmit =
        document.getElementById('reject-submit');

    const rejectReason =
        rejectForm?.querySelector('[name="reason"]');

    const rejectReasonCounter =
        document.getElementById('reject-reason-counter');


    const checkAll =
        document.getElementById('confirmation-check-all');

    const checkAllHeader =
        document.getElementById('confirmation-check-all-header');

    const selectedCount =
        document.getElementById('confirmation-selected-count');

    const selectedAmount =
        document.getElementById('confirmation-selected-amount');

    const selectedAmountWrapper =
        document.getElementById('confirmation-selected-amount-wrapper');


    /*
    |--------------------------------------------------------------------------
    | Row checks
    |--------------------------------------------------------------------------
    */

    const getRowChecks = () =>
        Array.from(
            document.querySelectorAll(
                '.confirmation-row-check'
            )
        );


    /*
    |--------------------------------------------------------------------------
    | Selection state
    |--------------------------------------------------------------------------
    */

    function refreshSelection() {

        const rows = getRowChecks();

        const checked =
            rows.filter(row => row.checked);

        const count =
            checked.length;

        const amount =
            checked.reduce(
                (total, row) =>
                    total +
                    (parseFloat(row.dataset.amount) || 0),
                0
            );


        if (selectedCount) {
            selectedCount.textContent = count;
        }


        if (selectedAmount) {
            selectedAmount.textContent =
                amount.toLocaleString(
                    'en-US',
                    {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }
                );
        }


        selectedAmountWrapper?.classList.toggle(
            'hidden',
            count === 0
        );


        /*
        |--------------------------------------------------------------------------
        | Master checkbox
        |--------------------------------------------------------------------------
        */

        const allSelected =
            rows.length > 0 &&
            checked.length === rows.length;

        const partiallySelected =
            checked.length > 0 &&
            checked.length < rows.length;


        if (checkAll) {

            checkAll.checked = allSelected;

            checkAll.indeterminate =
                partiallySelected;

        }


        if (checkAllHeader) {

            checkAllHeader.checked =
                allSelected;

            checkAllHeader.indeterminate =
                partiallySelected;

        }


        /*
        |--------------------------------------------------------------------------
        | Selection-dependent actions
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll('[data-needs-selection]')
            .forEach(button => {

                button.disabled =
                    count === 0;

            });

    }


    /*
    |--------------------------------------------------------------------------
    | Select all
    |--------------------------------------------------------------------------
    */

    function setAllSelection(checked) {

        getRowChecks().forEach(row => {

            row.checked = checked;

        });

        refreshSelection();
    }


    checkAll?.addEventListener(
        'change',
        function () {

            setAllSelection(
                this.checked
            );

        }
    );


    checkAllHeader?.addEventListener(
        'change',
        function () {

            setAllSelection(
                this.checked
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Row selection
    |--------------------------------------------------------------------------
    |
    | Event delegation prevents adding a separate listener to every row.
    | Better when the table becomes large.
    |
    */

    document.addEventListener(
        'change',
        function (event) {

            if (
                event.target.matches(
                    '.confirmation-row-check'
                )
            ) {

                refreshSelection();

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Copy helper
    |--------------------------------------------------------------------------
    */

    async function copyValue(value, button = null) {

        if (
            value === null ||
            value === undefined ||
            value === '' ||
            value === '-'
        ) {
            return;
        }


        try {

            await navigator.clipboard.writeText(
                String(value)
            );

        } catch (error) {

            /*
            |--------------------------------------------------------------------------
            | Browser fallback
            |--------------------------------------------------------------------------
            */

            const textarea =
                document.createElement('textarea');

            textarea.value =
                String(value);

            textarea.style.position =
                'fixed';

            textarea.style.opacity =
                '0';

            document.body.appendChild(
                textarea
            );

            textarea.select();

            document.execCommand(
                'copy'
            );

            textarea.remove();
        }


        if (!button) {
            return;
        }


        const original =
            button.innerHTML;

        button.innerHTML =
            '<i class="fa-solid fa-check text-[10px]"></i>';

        button.classList.add(
            'text-brand'
        );


        setTimeout(() => {

            button.innerHTML =
                original;

        }, 1000);

    }


    /*
    |--------------------------------------------------------------------------
    | Copy individual value
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'click',
        function (event) {

            const button =
                event.target.closest(
                    '[data-copy-value]'
                );

            if (!button) {
                return;
            }


            copyValue(
                button.dataset.copyValue,
                button
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Copy selected receipts
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'click',
        function (event) {

            const button =
                event.target.closest(
                    '[data-copy-selected-receipts]'
                );

            if (!button) {
                return;
            }


            const receipts =
                getRowChecks()
                    .filter(row => row.checked)
                    .map(row => row.dataset.receipt)
                    .filter(Boolean);


            if (!receipts.length) {
                return;
            }


            copyValue(
                receipts.join('\n'),
                button
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Open reject modal
    |--------------------------------------------------------------------------
    */

    function openReject(id) {

        const button =
            document.querySelector(
                `[data-reject="${CSS.escape(String(id))}"]`
            );


        if (!button) {
            return;
        }


        rejectForm.action =
            rejectUrl.replace(
                '__ID__',
                id
            );


        rejectId.value =
            id;


        rejectReceipt.textContent =
            button.dataset.receipt || '-';


        if (rejectReason) {
            rejectReason.value = '';
        }


        updateReasonCounter();


        rejectSubmit.disabled = false;

        rejectSubmit.classList.remove(
            'opacity-70',
            'cursor-wait'
        );


        rejectModal.showModal();


        /*
        |--------------------------------------------------------------------------
        | Focus reason automatically.
        |--------------------------------------------------------------------------
        */

        setTimeout(() => {

            rejectReason?.focus();

        }, 50);

    }


    /*
    |--------------------------------------------------------------------------
    | Reject button
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'click',
        function (event) {

            const button =
                event.target.closest(
                    '[data-reject]'
                );

            if (!button) {
                return;
            }


            openReject(
                button.dataset.reject
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Reason counter
    |--------------------------------------------------------------------------
    */

    function updateReasonCounter() {

        if (!rejectReasonCounter) {
            return;
        }


        const length =
            rejectReason?.value?.length || 0;


        rejectReasonCounter.textContent =
            length + ' / 255';

    }


    rejectReason?.addEventListener(
        'input',
        updateReasonCounter
    );


    /*
    |--------------------------------------------------------------------------
    | Reject submit protection
    |--------------------------------------------------------------------------
    */

    rejectForm?.addEventListener(
        'submit',
        function (event) {

            if (
                !rejectReason?.value.trim()
            ) {

                event.preventDefault();

                rejectReason?.focus();

                return;
            }


            rejectSubmit.disabled =
                true;


            rejectSubmit.classList.add(
                'opacity-70',
                'cursor-wait'
            );


            const label =
                rejectSubmit.querySelector(
                    'span'
                );


            if (label) {
                label.textContent =
                    'جاري الرفض...';
            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Approve submit protection
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'submit',
        function (event) {

            const form =
                event.target.closest(
                    '[data-approve-form]'
                );

            if (!form) {
                return;
            }


            const button =
                form.querySelector(
                    '[data-approve-button]'
                );


            if (!button) {
                return;
            }


            button.disabled =
                true;


            button.classList.add(
                'opacity-70',
                'cursor-wait'
            );


            const label =
                button.querySelector(
                    'span'
                );


            if (label) {

                label.textContent =
                    'جاري التأكيد...';

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Ctrl + K
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'keydown',
        function (event) {

            if (
                event.ctrlKey &&
                event.key.toLowerCase() === 'k'
            ) {

                event.preventDefault();

                const search =
                    document.getElementById(
                        'confirmation-search'
                    );

                search?.focus();
                search?.select();

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Initial state
    |--------------------------------------------------------------------------
    */

    refreshSelection();

    updateReasonCounter();


    /*
    |--------------------------------------------------------------------------
    | Validation failed
    |--------------------------------------------------------------------------
    */

    @if($errors->has('reason') && old('_reject_id'))

        openReject(
            @json(old('_reject_id'))
        );

        if (rejectReason) {

            rejectReason.value =
                @json(old('reason', ''));

            updateReasonCounter();

        }

    @endif

})();
</script>

@endpush