{{-- resources/views/banks/partials/_visit-display.blade.php --}}

<x-modal
    id="visitDisplayModal"
    title="تفاصيل الزيارة"
    width="48rem"
>

    <div
        id="visitDisplayContent"
        class="space-y-5"
    >

        {{-- ============================================================
            HEADER
        ============================================================= --}}

        <div class="rounded-xl border border-border bg-panel/40 p-4">

            <div class="flex flex-wrap items-center justify-between gap-4">

                <div class="flex min-w-0 items-center gap-3">

                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-brand/10 text-brand">
                        <i class="fa-solid fa-person-walking text-lg"></i>
                    </div>

                    <div class="min-w-0">

                        <div class="flex flex-wrap items-center gap-2">

                            <h3
                                id="display-client-name"
                                class="truncate text-base font-bold text-fg"
                            >
                                -
                            </h3>

                            <span
                                id="display-status"
                                class="badge badge-neutral"
                            >
                                -
                            </span>

                        </div>

                        <p
                            id="display-client-code"
                            class="mt-1 font-mono text-[11px] text-dim"
                        >
                            -
                        </p>

                    </div>

                </div>

                <div class="text-left">

                    <p class="text-[10px] text-dim">
                        رقم الزيارة
                    </p>

                    <p
                        id="display-visit-id"
                        class="font-mono text-sm font-bold text-fg"
                    >
                        -
                    </p>

                </div>

            </div>

        </div>


        {{-- ============================================================
            VISIT INFORMATION
        ============================================================= --}}

        <div>

            <div class="mb-3 flex items-center gap-2">

                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-info/10 text-info">
                    <i class="fa-solid fa-circle-info text-xs"></i>
                </div>

                <div>

                    <h3 class="text-sm font-bold text-fg">
                        معلومات الزيارة
                    </h3>

                    <p class="text-[10px] text-dim">
                        البيانات الأساسية للزيارة
                    </p>

                </div>

            </div>


            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

                {{-- Appointment --}}
                <div class="rounded-xl border border-border bg-card p-3">

                    <div class="flex items-center gap-3">

                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-cyan/10 text-cyan">
                            <i class="fa-solid fa-calendar-day text-xs"></i>
                        </div>

                        <div class="min-w-0">

                            <p class="text-[10px] text-dim">
                                موعد الزيارة
                            </p>

                            <p
                                id="display-when"
                                class="mt-1 text-xs font-semibold text-fg"
                            >
                                -
                            </p>

                        </div>

                    </div>

                </div>


                {{-- Collector --}}
                <div class="rounded-xl border border-border bg-card p-3">

                    <div class="flex items-center gap-3">

                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-info/10 text-info">
                            <i class="fa-solid fa-user-tie text-xs"></i>
                        </div>

                        <div class="min-w-0">

                            <p class="text-[10px] text-dim">
                                المحصل المسؤول
                            </p>

                            <p
                                id="display-collector"
                                class="mt-1 text-xs font-semibold text-fg"
                            >
                                -
                            </p>

                        </div>

                    </div>

                </div>


                {{-- Address --}}
                <div class="rounded-xl border border-border bg-card p-3 sm:col-span-2">

                    <div class="flex items-start gap-3">

                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-warning/10 text-warning">
                            <i class="fa-solid fa-location-dot text-xs"></i>
                        </div>

                        <div class="min-w-0 flex-1">

                            <p class="text-[10px] text-dim">
                                عنوان الزيارة
                            </p>

                            <p
                                id="display-address"
                                class="mt-1 whitespace-normal text-xs leading-6 text-fg"
                            >
                                -
                            </p>

                        </div>

                        <button
                            type="button"
                            id="copyVisitAddress"
                            class="btn btn-secondary btn-sm shrink-0"
                            title="نسخ العنوان"
                        >
                            <i class="fa-regular fa-copy text-xs"></i>
                            نسخ
                        </button>

                    </div>

                </div>

            </div>

        </div>


        {{-- ============================================================
            RESULT
        ============================================================= --}}

        <div>

            <div class="mb-3 flex items-center gap-2">

                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand/10 text-brand">
                    <i class="fa-solid fa-clipboard-check text-xs"></i>
                </div>

                <div>

                    <h3 class="text-sm font-bold text-fg">
                        نتيجة الزيارة
                    </h3>

                    <p class="text-[10px] text-dim">
                        آخر نتيجة وملاحظات مسجلة
                    </p>

                </div>

            </div>


            <div class="rounded-xl border border-border bg-card p-4">

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                    <div>

                        <p class="text-[10px] text-dim">
                            النتيجة
                        </p>

                        <p
                            id="display-outcome"
                            class="mt-1 text-sm font-bold text-fg"
                        >
                            لم يتم تسجيل نتيجة بعد
                        </p>

                    </div>

                    <div>

                        <p class="text-[10px] text-dim">
                            الحالة الحالية
                        </p>

                        <p
                            id="display-status-text"
                            class="mt-1 text-sm font-bold text-fg"
                        >
                            -
                        </p>

                    </div>

                </div>


                <div class="mt-4 border-t border-border pt-4">

                    <div class="flex items-start gap-3">

                        <i class="fa-solid fa-note-sticky mt-1 text-xs text-cyan"></i>

                        <div class="min-w-0 flex-1">

                            <p class="text-[10px] text-dim">
                                الملاحظات
                            </p>

                            <p
                                id="display-notes"
                                class="mt-1 whitespace-pre-wrap text-xs leading-6 text-fg"
                            >
                                لا توجد ملاحظات مسجلة.
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- ============================================================
            QUICK SUMMARY
        ============================================================= --}}

        <div
            id="displayCompletedNotice"
            class="hidden rounded-xl border border-brand/20 bg-brand/10 p-3"
        >

            <div class="flex items-start gap-3">

                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand/10 text-brand">
                    <i class="fa-solid fa-circle-check text-xs"></i>
                </div>

                <div>

                    <p class="text-xs font-bold text-fg">
                        تم تسجيل نتيجة الزيارة
                    </p>

                    <p class="mt-1 text-[10px] leading-5 text-dim">
                        يمكنك مراجعة النتيجة والملاحظات المسجلة أعلاه.
                    </p>

                </div>

            </div>

        </div>


        {{-- ============================================================
            ACTIONS
        ============================================================= --}}

        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-border pt-4">

            <div class="flex items-center gap-2 text-[10px] text-dim">

                <i class="fa-solid fa-clock-rotate-left text-info"></i>

                <span>
                    بيانات الزيارة الحالية
                </span>

            </div>

            <div class="flex gap-2">

                <button
                    type="button"
                    id="displayResultButton"
                    class="btn btn-primary btn-sm"
                >
                    <i class="fa-solid fa-clipboard-check text-xs"></i>
                    تسجيل النتيجة
                </button>

                <button
                    type="button"
                    class="btn btn-secondary btn-sm"
                    data-close-modal
                >
                    إغلاق
                </button>

            </div>

        </div>

    </div>

</x-modal>


@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', () => {

    /*
    |--------------------------------------------------------------------------
    | Elements
    |--------------------------------------------------------------------------
    */

    const modal =
        document.getElementById(
            'visitDisplayModal'
        );

    const clientName =
        document.getElementById(
            'display-client-name'
        );

    const clientCode =
        document.getElementById(
            'display-client-code'
        );

    const visitId =
        document.getElementById(
            'display-visit-id'
        );

    const status =
        document.getElementById(
            'display-status'
        );

    const statusText =
        document.getElementById(
            'display-status-text'
        );

    const when =
        document.getElementById(
            'display-when'
        );

    const collector =
        document.getElementById(
            'display-collector'
        );

    const address =
        document.getElementById(
            'display-address'
        );

    const outcome =
        document.getElementById(
            'display-outcome'
        );

    const notes =
        document.getElementById(
            'display-notes'
        );

    const resultButton =
        document.getElementById(
            'displayResultButton'
        );

    const completedNotice =
        document.getElementById(
            'displayCompletedNotice'
        );

    const copyAddressButton =
        document.getElementById(
            'copyVisitAddress'
        );

    /*
    |--------------------------------------------------------------------------
    | Current visit
    |--------------------------------------------------------------------------
    */

    let currentVisitButton = null;

    /*
    |--------------------------------------------------------------------------
    | Open visit display
    |--------------------------------------------------------------------------
    */

    function openVisitDisplay(button) {

        if (!button) {
            return;
        }

        currentVisitButton =
            button;

        const data =
            button.dataset;

        /*
        | Basic information
        */

        if (clientName) {
            clientName.textContent =
                data.client || '-';
        }

        if (clientCode) {
            clientCode.textContent =
                data.clientCode || '-';
        }

        if (visitId) {
            visitId.textContent =
                data.viewVisit || '-';
        }

        if (when) {
            when.textContent =
                data.when || '-';
        }

        if (collector) {
            collector.textContent =
                data.collector || '-';
        }

        if (address) {
            address.textContent =
                data.address || 'لا يوجد عنوان مسجل.';
        }

        /*
        | Status
        */

        const statusLabel =
            data.statusLabel ||
            data.status ||
            '-';

        if (status) {

            status.textContent =
                statusLabel;

            status.className =
                'badge badge-neutral';

            const statusValue =
                data.status || '';

            if (
                statusValue === 'completed'
            ) {

                status.classList.add(
                    'badge-success'
                );

            } else if (
                statusValue === 'scheduled'
            ) {

                status.classList.add(
                    'badge-info'
                );

            } else if (
                statusValue === 'missed'
            ) {

                status.classList.add(
                    'badge-danger'
                );

            } else if (
                statusValue === 'cancelled'
            ) {

                status.classList.add(
                    'badge-danger'
                );

            }

        }

        if (statusText) {
            statusText.textContent =
                statusLabel;
        }

        /*
        | Outcome
        */

        const outcomeValue =
            data.outcome || '';

        if (outcome) {

            outcome.textContent =
                outcomeValue ||
                'لم يتم تسجيل نتيجة بعد';

            if (outcomeValue) {

                outcome.classList.add(
                    'text-brand'
                );

            } else {

                outcome.classList.remove(
                    'text-brand'
                );

            }

        }

        /*
        | Notes
        */

        if (notes) {

            notes.textContent =
                data.notes ||
                'لا توجد ملاحظات مسجلة.';

        }

        /*
        | Completed notice
        */

        if (completedNotice) {

            completedNotice.classList.toggle(
                'hidden',
                !outcomeValue
            );

        }

        /*
        | Result button
        */

        if (resultButton) {

            /*
            | We keep the button available so
            | the user can update the result.
            */

            resultButton.dataset.result =
                data.viewVisit || '';

        }

        /*
        | Show modal
        */

        modal?.showModal();

    }

    /*
    |--------------------------------------------------------------------------
    | Open from client name / view button
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'click',
        event => {

            const button =
                event.target.closest(
                    '[data-view-visit]'
                );

            if (!button) {
                return;
            }

            openVisitDisplay(
                button
            );

        }
    );

    /*
    |--------------------------------------------------------------------------
    | Copy address
    |--------------------------------------------------------------------------
    */

    copyAddressButton?.addEventListener(
        'click',
        async () => {

            const value =
                address?.textContent?.trim();

            if (
                !value ||
                value === 'لا يوجد عنوان مسجل.'
            ) {
                return;
            }

            try {

                await navigator.clipboard.writeText(
                    value
                );

                copyAddressButton.innerHTML = `
                    <i class="fa-solid fa-check text-xs"></i>
                    تم النسخ
                `;

                setTimeout(() => {

                    copyAddressButton.innerHTML = `
                        <i class="fa-regular fa-copy text-xs"></i>
                        نسخ
                    `;

                }, 1500);

            } catch (error) {

                /*
                | Clipboard may be unavailable
                | in some browser contexts.
                */

                console.warn(
                    'Could not copy address.',
                    error
                );

            }

        }
    );

    /*
    |--------------------------------------------------------------------------
    | Go directly to result form
    |--------------------------------------------------------------------------
    */

    resultButton?.addEventListener(
        'click',
        () => {

            if (!currentVisitButton) {
                return;
            }

            const id =
                currentVisitButton.dataset.viewVisit;

            /*
            | Close current dialog
            */

            modal?.close();

            /*
            | Existing result system
            */

            if (
                typeof window.openResult ===
                'function'
            ) {

                window.openResult(id);

            } else {

                /*
                | Fallback:
                | find the existing result button
                */

                const resultTrigger =
                    document.querySelector(
                        `[data-result="${id}"]`
                    );

                resultTrigger?.click();

            }

        }
    );

});

</script>

@endpush