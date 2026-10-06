{{-- resources/views/banks/partials/_schedule-visit-form.blade.php --}}

@php
    /*
    |--------------------------------------------------------------------------
    | Existing client collection
    |--------------------------------------------------------------------------
    | نستخدم نفس $clients الموجود أصلاً.
    | لا يوجد API جديد ولا Route جديد.
    */

    $visitClients = collect($clients ?? []);
@endphp

<x-modal
    id="scheduleModal"
    title="جدولة زيارة ميدانية"
    width="46rem"
>

    <form
        id="scheduleVisitForm"
        method="POST"
        action="{{ route('banks.visits.store', $bank->id) }}"
        class="space-y-5"
        autocomplete="off"
    >

        @csrf

        <input
            type="hidden"
            name="_form"
            value="schedule"
        >

        {{-- ============================================================
            CUSTOMER
        ============================================================= --}}

        <div class="rounded-xl border border-border bg-panel/40 p-4">

            <div class="mb-4 flex items-center justify-between gap-3">

                <div class="flex items-center gap-3">

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-info/10 text-info">
                        <i class="fa-solid fa-user"></i>
                    </div>

                    <div>

                        <h3 class="text-sm font-bold text-fg">
                            بيانات العميل
                        </h3>

                        <p class="mt-0.5 text-[11px] text-dim">
                            ابحث عن العميل بالاسم أو الكود / ID
                        </p>

                    </div>

                </div>

                <span
                    id="visitClientResultCount"
                    class="text-[11px] text-dim"
                >
                    {{ $visitClients->count() }} عميل
                </span>

            </div>

            {{-- Customer search --}}
            <div class="relative">

                <i class="fa-solid fa-magnifying-glass absolute right-3 top-1/2 -translate-y-1/2 text-xs text-dim"></i>

                <input
                    id="visitClientSearch"
                    type="search"
                    class="form-input pr-9 pl-10"
                    placeholder="ابحث باسم العميل أو الكود..."
                    autocomplete="off"
                    spellcheck="false"
                >

                <button
                    type="button"
                    id="clearVisitClientSearch"
                    class="absolute left-3 top-1/2 hidden -translate-y-1/2 text-xs text-dim hover:text-fg"
                    title="مسح البحث"
                    aria-label="مسح البحث"
                >
                    <i class="fa-solid fa-xmark"></i>
                </button>

            </div>

            {{-- Search results --}}
            <div
                id="visitClientSearchResults"
                class="mt-2 hidden max-h-56 overflow-y-auto rounded-xl border border-border bg-card"
            ></div>

            {{-- No results --}}
            <div
                id="visitClientNoResults"
                class="mt-2 hidden rounded-xl border border-border bg-card p-4 text-center"
            >

                <i class="fa-solid fa-user-slash mb-2 text-lg text-dim"></i>

                <p class="text-xs font-semibold text-fg">
                    لم يتم العثور على عميل
                </p>

                <p class="mt-1 text-[11px] text-dim">
                    جرّب البحث باستخدام اسم مختلف أو كود العميل.
                </p>

            </div>

            {{-- Existing backend select --}}
            <div class="mt-3">

                <x-select-field
                    name="client_code"
                    label="العميل"
                    :options="$clients"
                    placeholder="اختر العميل..."
                />

            </div>

            {{-- Selected client --}}
            <div
                id="visitSelectedClient"
                class="mt-3 hidden rounded-xl border border-brand/20 bg-brand/[0.04] p-3"
            >

                <div class="flex items-center justify-between gap-3">

                    <div class="flex items-center gap-3">

                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand/10 text-brand">
                            <i class="fa-solid fa-user-check"></i>
                        </div>

                        <div>

                            <p class="text-[11px] text-dim">
                                العميل المختار
                            </p>

                            <p
                                id="visitSelectedClientName"
                                class="text-sm font-bold text-fg"
                            ></p>

                            <p
                                id="visitSelectedClientCode"
                                class="mt-0.5 font-mono text-[10px] text-dim"
                            ></p>

                        </div>

                    </div>

                    <button
                        type="button"
                        id="changeVisitClient"
                        class="btn btn-secondary btn-sm"
                    >
                        تغيير
                    </button>

                </div>

            </div>

            <div class="mt-2 flex items-center gap-2 text-[10px] text-dim">

                <i class="fa-solid fa-circle-info"></i>

                <span>
                    يمكنك البحث بالاسم أو كود العميل، ثم اختيار العميل من النتائج.
                </span>

            </div>

        </div>

        {{-- ============================================================
            VISIT INFORMATION
        ============================================================= --}}

        <div class="rounded-xl border border-border bg-panel/40 p-4">

            <div class="mb-4 flex items-center gap-3">

                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan/10 text-cyan">
                    <i class="fa-solid fa-calendar-days"></i>
                </div>

                <div>

                    <h3 class="text-sm font-bold text-fg">
                        تفاصيل الزيارة
                    </h3>

                    <p class="mt-0.5 text-[11px] text-dim">
                        حدد المحصل والموعد والعنوان
                    </p>

                </div>

            </div>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">

                <x-select-field
                    name="user_id"
                    label="المحصل"
                    :options="$employees"
                    placeholder="اختر المحصل..."
                />

                <x-form-field
                    name="scheduled_at"
                    label="موعد الزيارة"
                    type="datetime-local"
                />

            </div>

            {{-- Date/time helper --}}
            <div class="mt-3 rounded-xl border border-info/20 bg-info/10 p-3">

                <div class="flex items-start gap-2">

                    <i class="fa-solid fa-lightbulb mt-0.5 text-xs text-info"></i>

                    <div>

                        <p class="text-xs font-semibold text-fg">
                            تنبيه للموعد
                        </p>

                        <p
                            id="visitScheduleHint"
                            class="mt-1 text-[11px] leading-5 text-dim"
                        >
                            اختر تاريخ ووقت الزيارة ليظهر لك تنبيه الموعد هنا.
                        </p>

                    </div>

                </div>

            </div>

            <div class="mt-3">

                <x-form-field
                    name="address"
                    label="عنوان الزيارة"
                    placeholder="اتركه فارغاً لاستخدام عنوان العميل"
                />

                <p class="mt-1 text-[10px] text-dim">
                    إذا تركت الحقل فارغاً سيتم استخدام عنوان العميل المسجل بالنظام.
                </p>

            </div>

        </div>

        {{-- ============================================================
            NOTES
        ============================================================= --}}

        <div class="rounded-xl border border-border bg-panel/40 p-4">

            <div class="mb-3 flex items-center justify-between gap-3">

                <div class="flex items-center gap-2">

                    <i class="fa-solid fa-note-sticky text-xs text-warning"></i>

                    <span class="text-sm font-bold text-fg">
                        ملاحظات الزيارة
                    </span>

                </div>

                <span
                    id="visitNotesCounter"
                    class="text-[10px] text-dim"
                >
                    0 حرف
                </span>

            </div>

            <x-textarea-field
                name="notes"
                label=""
                rows="3"
                placeholder="أضف أي معلومات مهمة للمحصل قبل الزيارة..."
            />

            <div class="mt-2 flex items-center gap-2 text-[10px] text-dim">

                <i class="fa-solid fa-circle-info"></i>

                <span>
                    مثال: العميل متاح بعد الساعة 4، العنوان بجوار...
                </span>

            </div>

        </div>

        {{-- ============================================================
            QUICK VALIDATION
        ============================================================= --}}

        <div
            id="scheduleVisitValidation"
            class="hidden rounded-xl border border-danger/20 bg-danger/10 p-3"
        >

            <div class="flex items-start gap-2">

                <i class="fa-solid fa-triangle-exclamation mt-0.5 text-danger"></i>

                <div>

                    <p class="text-xs font-bold text-fg">
                        يرجى مراجعة البيانات
                    </p>

                    <ul
                        id="scheduleVisitValidationList"
                        class="mt-1 list-disc pr-4 text-[11px] text-dim"
                    ></ul>

                </div>

            </div>

        </div>

        {{-- ============================================================
            ACTIONS
        ============================================================= --}}

        <div class="flex flex-wrap items-center justify-between gap-3">

            <div class="flex items-center gap-2 text-[10px] text-dim">

                <i class="fa-solid fa-shield-halved"></i>

                <span>
                    سيتم حفظ الزيارة بالحالة المجدولة.
                </span>

            </div>

            <div class="flex gap-2">

                <button
                    type="submit"
                    class="btn btn-primary"
                    id="scheduleVisitSubmit"
                >
                    <i class="fa-solid fa-calendar-check text-xs"></i>
                    جدولة الزيارة
                </button>

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-close-modal
                >
                    إلغاء
                </button>

            </div>

        </div>

    </form>

</x-modal>


@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', () => {

    /*
    |--------------------------------------------------------------------------
    | Client data
    |--------------------------------------------------------------------------
    |
    | نقرأ الـ options الموجودة بالفعل في select.
    | لا يوجد API أو endpoint جديد.
    |
    */

    const clientSelect =
        document.querySelector(
            '#scheduleModal select[name="client_code"]'
        );

    const clientSearch =
        document.getElementById(
            'visitClientSearch'
        );

    const searchResults =
        document.getElementById(
            'visitClientSearchResults'
        );

    const noResults =
        document.getElementById(
            'visitClientNoResults'
        );

    const resultCount =
        document.getElementById(
            'visitClientResultCount'
        );

    const clearSearch =
        document.getElementById(
            'clearVisitClientSearch'
        );

    const selectedClient =
        document.getElementById(
            'visitSelectedClient'
        );

    const selectedClientName =
        document.getElementById(
            'visitSelectedClientName'
        );

    const selectedClientCode =
        document.getElementById(
            'visitSelectedClientCode'
        );

    const changeClient =
        document.getElementById(
            'changeVisitClient'
        );

    const scheduleForm =
        document.getElementById(
            'scheduleVisitForm'
        );

    const scheduledAt =
        scheduleForm
            ? scheduleForm.querySelector(
                '[name="scheduled_at"]'
            )
            : null;

    const scheduleHint =
        document.getElementById(
            'visitScheduleHint'
        );

    const notes =
        scheduleForm
            ? scheduleForm.querySelector(
                'textarea[name="notes"]'
            )
            : null;

    const notesCounter =
        document.getElementById(
            'visitNotesCounter'
        );

    const submitButton =
        document.getElementById(
            'scheduleVisitSubmit'
        );

    const validationBox =
        document.getElementById(
            'scheduleVisitValidation'
        );

    const validationList =
        document.getElementById(
            'scheduleVisitValidationList'
        );

    /*
    |--------------------------------------------------------------------------
    | Convert existing select options to searchable objects
    |--------------------------------------------------------------------------
    */

    const clients = [];

    if (clientSelect) {

        Array.from(
            clientSelect.options
        ).forEach(option => {

            if (!option.value) {
                return;
            }

            clients.push({
                value: option.value,
                label: option.textContent.trim()
            });

        });
    }

    /*
    |--------------------------------------------------------------------------
    | Escape HTML
    |--------------------------------------------------------------------------
    */

    function escapeHtml(value) {

        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');

    }

    /*
    |--------------------------------------------------------------------------
    | Search clients
    |--------------------------------------------------------------------------
    */

    function searchClients() {

        if (!clientSearch || !searchResults) {
            return;
        }

        const query =
            clientSearch.value
                .trim()
                .toLowerCase();

        clearSearch?.classList.toggle(
            'hidden',
            query === ''
        );

        searchResults.innerHTML = '';

        noResults?.classList.add('hidden');

        if (query === '') {

            searchResults.classList.add(
                'hidden'
            );

            if (resultCount) {
                resultCount.textContent =
                    `${clients.length} عميل`;
            }

            return;
        }

        const matches =
            clients
                .filter(client =>
                    client.label
                        .toLowerCase()
                        .includes(query) ||
                    client.value
                        .toLowerCase()
                        .includes(query)
                )
                .slice(0, 50);

        if (resultCount) {

            resultCount.textContent =
                `${matches.length} نتيجة`;
        }

        if (!matches.length) {

            searchResults.classList.add(
                'hidden'
            );

            noResults?.classList.remove(
                'hidden'
            );

            return;
        }

        searchResults.classList.remove(
            'hidden'
        );

        matches.forEach(client => {

            const item =
                document.createElement('button');

            item.type = 'button';

            item.className =
                'flex w-full items-center justify-between gap-3 border-b border-border px-3 py-3 text-right last:border-b-0 hover:bg-white/[0.03]';

            item.innerHTML = `
                <span class="flex min-w-0 items-center gap-3">

                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-info/10 text-info">
                        <i class="fa-solid fa-user text-xs"></i>
                    </span>

                    <span class="min-w-0">

                        <span class="block truncate text-xs font-semibold text-fg">
                            ${escapeHtml(client.label)}
                        </span>

                        <span class="mt-0.5 block font-mono text-[10px] text-dim">
                            ${escapeHtml(client.value)}
                        </span>

                    </span>

                </span>

                <i class="fa-solid fa-chevron-left text-[10px] text-dim"></i>
            `;

            item.addEventListener(
                'click',
                () => {

                    selectClient(client);

                }
            );

            searchResults.appendChild(
                item
            );

        });
    }

    /*
    |--------------------------------------------------------------------------
    | Select client
    |--------------------------------------------------------------------------
    */

    function selectClient(client) {

        if (!clientSelect) {
            return;
        }

        clientSelect.value =
            client.value;

        /*
        | Trigger existing select listeners
        */
        clientSelect.dispatchEvent(
            new Event('change', {
                bubbles: true
            })
        );

        if (selectedClient) {
            selectedClient.classList.remove(
                'hidden'
            );
        }

        if (selectedClientName) {
            selectedClientName.textContent =
                client.label;
        }

        if (selectedClientCode) {
            selectedClientCode.textContent =
                client.value;
        }

        if (clientSearch) {
            clientSearch.value =
                client.label;
        }

        searchResults?.classList.add(
            'hidden'
        );

        noResults?.classList.add(
            'hidden'
        );

        clearSearch?.classList.remove(
            'hidden'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Existing select change
    |--------------------------------------------------------------------------
    */

    if (clientSelect) {

        clientSelect.addEventListener(
            'change',
            () => {

                const selected =
                    clientSelect.options[
                        clientSelect.selectedIndex
                    ];

                if (
                    !selected ||
                    !selected.value
                ) {

                    selectedClient?.classList.add(
                        'hidden'
                    );

                    return;
                }

                const client = {
                    value: selected.value,
                    label: selected.textContent.trim()
                };

                if (selectedClient) {
                    selectedClient.classList.remove(
                        'hidden'
                    );
                }

                if (selectedClientName) {
                    selectedClientName.textContent =
                        client.label;
                }

                if (selectedClientCode) {
                    selectedClientCode.textContent =
                        client.value;
                }

            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Search events
    |--------------------------------------------------------------------------
    */

    clientSearch?.addEventListener(
        'input',
        searchClients
    );

    clientSearch?.addEventListener(
        'focus',
        () => {

            if (
                clientSearch.value.trim() !== ''
            ) {
                searchClients();
            }

        }
    );

    /*
    |--------------------------------------------------------------------------
    | Clear search
    |--------------------------------------------------------------------------
    */

    clearSearch?.addEventListener(
        'click',
        () => {

            if (clientSearch) {
                clientSearch.value = '';
                clientSearch.focus();
            }

            searchResults?.classList.add(
                'hidden'
            );

            noResults?.classList.add(
                'hidden'
            );

            if (resultCount) {
                resultCount.textContent =
                    `${clients.length} عميل`;
            }

        }
    );

    /*
    |--------------------------------------------------------------------------
    | Change selected client
    |--------------------------------------------------------------------------
    */

    changeClient?.addEventListener(
        'click',
        () => {

            selectedClient?.classList.add(
                'hidden'
            );

            clientSearch?.focus();

            if (clientSearch) {
                clientSearch.select();
            }

        }
    );

    /*
    |--------------------------------------------------------------------------
    | Click outside search results
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'click',
        event => {

            if (
                !event.target.closest(
                    '#visitClientSearch'
                ) &&
                !event.target.closest(
                    '#visitClientSearchResults'
                )
            ) {

                searchResults?.classList.add(
                    'hidden'
                );

            }

        }
    );

    /*
    |--------------------------------------------------------------------------
    | Schedule date/time helper
    |--------------------------------------------------------------------------
    */

    function updateScheduleHint() {

        if (
            !scheduledAt ||
            !scheduleHint ||
            !scheduledAt.value
        ) {

            if (scheduleHint) {
                scheduleHint.textContent =
                    'اختر تاريخ ووقت الزيارة ليظهر لك تنبيه الموعد هنا.';
            }

            return;
        }

        const selected =
            new Date(
                scheduledAt.value
            );

        if (Number.isNaN(selected.getTime())) {
            return;
        }

        const now =
            new Date();

        const diff =
            selected.getTime() -
            now.getTime();

        const hours =
            Math.round(
                diff / (1000 * 60 * 60)
            );

        if (diff < 0) {

            scheduleHint.textContent =
                'تنبيه: الموعد المحدد في الماضي. اختر موعداً مستقبلياً.';

            scheduleHint.classList.add(
                'text-danger'
            );

            return;
        }

        scheduleHint.classList.remove(
            'text-danger'
        );

        if (hours < 1) {

            scheduleHint.textContent =
                'الزيارة خلال أقل من ساعة من الآن.';

        } else if (hours < 24) {

            scheduleHint.textContent =
                `الزيارة خلال حوالي ${hours} ساعة.`;

        } else {

            const days =
                Math.round(hours / 24);

            scheduleHint.textContent =
                `الزيارة بعد حوالي ${days} يوم.`;
        }
    }

    scheduledAt?.addEventListener(
        'change',
        updateScheduleHint
    );

    scheduledAt?.addEventListener(
        'input',
        updateScheduleHint
    );

    /*
    |--------------------------------------------------------------------------
    | Notes counter
    |--------------------------------------------------------------------------
    */

    function updateNotesCounter() {

        if (!notes || !notesCounter) {
            return;
        }

        notesCounter.textContent =
            `${notes.value.length} حرف`;
    }

    notes?.addEventListener(
        'input',
        updateNotesCounter
    );

    updateNotesCounter();

    /*
    |--------------------------------------------------------------------------
    | Client-side validation
    |--------------------------------------------------------------------------
    */

    function validateScheduleForm() {

        const errors = [];

        if (
            clientSelect &&
            !clientSelect.value
        ) {

            errors.push(
                'اختر العميل.'
            );
        }

        const collectorSelect =
            scheduleForm?.querySelector(
                'select[name="user_id"]'
            );

        if (
            collectorSelect &&
            !collectorSelect.value
        ) {

            errors.push(
                'اختر المحصل المسؤول عن الزيارة.'
            );
        }

        if (
            scheduledAt &&
            !scheduledAt.value
        ) {

            errors.push(
                'حدد موعد الزيارة.'
            );

        } else if (scheduledAt?.value) {

            const selected =
                new Date(
                    scheduledAt.value
                );

            if (
                !Number.isNaN(
                    selected.getTime()
                ) &&
                selected.getTime() <
                    new Date().getTime()
            ) {

                errors.push(
                    'موعد الزيارة يجب أن يكون في المستقبل.'
                );
            }
        }

        if (
            validationBox &&
            validationList
        ) {

            validationList.innerHTML = '';

            errors.forEach(error => {

                const li =
                    document.createElement('li');

                li.textContent = error;

                validationList.appendChild(
                    li
                );

            });

            validationBox.classList.toggle(
                'hidden',
                errors.length === 0
            );
        }

        return errors.length === 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    scheduleForm?.addEventListener(
        'submit',
        event => {

            if (!validateScheduleForm()) {

                event.preventDefault();

                return;
            }

            if (
                submitButton &&
                submitButton.dataset.submitted === '1'
            ) {

                event.preventDefault();

                return;
            }

            if (submitButton) {

                submitButton.dataset.submitted =
                    '1';

                submitButton.disabled =
                    true;

                submitButton.innerHTML = `
                    <i class="fa-solid fa-spinner fa-spin text-xs"></i>
                    جاري جدولة الزيارة...
                `;
            }

        }
    );

    /*
    |--------------------------------------------------------------------------
    | Ctrl + Enter -> submit
    |--------------------------------------------------------------------------
    */

    scheduleForm?.addEventListener(
        'keydown',
        event => {

            if (
                event.ctrlKey &&
                event.key === 'Enter'
            ) {

                event.preventDefault();

                scheduleForm.requestSubmit();

            }

        }
    );

    /*
    |--------------------------------------------------------------------------
    | Reset modal state after close
    |--------------------------------------------------------------------------
    */

    const scheduleModal =
        document.getElementById(
            'scheduleModal'
        );

    scheduleModal?.addEventListener(
        'close',
        () => {

            /*
            | لا نمسح القيم في حالة وجود validation errors
            | لأن Laravel سيعيد old values.
            */

            if (
                @json($errors->any() && old('_form') === 'schedule')
            ) {
                return;
            }

            if (clientSearch) {
                clientSearch.value = '';
            }

            searchResults?.classList.add(
                'hidden'
            );

            noResults?.classList.add(
                'hidden'
            );

        }
    );

});
</script>

@endpush