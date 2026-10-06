{{-- resources/views/banks/partials/_visit-result-form.blade.php --}}

<x-modal
    id="resultModal"
    title="تسجيل نتيجة الزيارة"
    width="42rem"
>

    <form
        id="resultForm"
        method="POST"
        action="#"
        class="space-y-5"
        autocomplete="off"
    >

        @csrf

        @method('PUT')

        {{-- ============================================================
            SYSTEM FIELDS
        ============================================================= --}}

        <input
            type="hidden"
            name="_form"
            value="result"
        >

        <input
            type="hidden"
            name="_visit_id"
            id="result-visit-id"
            value=""
        >

        <input
            type="hidden"
            name="action"
            value="result"
        >

        {{-- ============================================================
            VISIT SUMMARY
        ============================================================= --}}

        <div class="rounded-xl border border-border bg-panel/40 p-4">

            <div class="mb-4 flex items-center justify-between gap-3">

                <div class="flex items-center gap-3">

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand/10 text-brand">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>

                    <div>

                        <h3 class="text-sm font-bold text-fg">
                            نتيجة الزيارة
                        </h3>

                        <p class="mt-0.5 text-[11px] text-dim">
                            سجّل ما حدث فعلياً أثناء زيارة العميل
                        </p>

                    </div>

                </div>

                <span
                    id="resultVisitStatus"
                    class="badge badge-neutral"
                >
                    زيارة مجدولة
                </span>

            </div>


            {{-- ========================================================
                Client
            ========================================================= --}}

            <div class="rounded-xl border border-border bg-card p-3">

                <div class="flex items-center justify-between gap-3">

                    <div class="flex min-w-0 items-center gap-3">

                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-info/10 text-info">
                            <i class="fa-solid fa-user"></i>
                        </div>

                        <div class="min-w-0">

                            <p class="text-[10px] text-dim">
                                العميل
                            </p>

                            <p
                                id="result-client"
                                class="truncate text-sm font-bold text-fg"
                            >
                                -
                            </p>

                            <p
                                id="result-client-code"
                                class="mt-0.5 font-mono text-[10px] text-dim"
                            >
                                -
                            </p>

                        </div>

                    </div>

                    <div
                        id="resultVisitIdDisplay"
                        class="text-left"
                    >

                        <p class="text-[10px] text-dim">
                            رقم الزيارة
                        </p>

                        <p
                            id="result-visit-id-display"
                            class="font-mono text-xs font-bold text-fg"
                        >
                            -
                        </p>

                    </div>

                </div>

            </div>


            {{-- ========================================================
                Visit metadata
            ========================================================= --}}

            <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">

                {{-- Date --}}

                <div class="rounded-lg border border-border bg-card px-3 py-2">

                    <div class="flex items-center gap-2">

                        <i class="fa-solid fa-calendar-day text-[11px] text-cyan"></i>

                        <div>

                            <p class="text-[10px] text-dim">
                                الموعد
                            </p>

                            <p
                                id="result-visit-date"
                                class="text-xs font-semibold text-fg"
                            >
                                -
                            </p>

                        </div>

                    </div>

                </div>


                {{-- Collector --}}

                <div class="rounded-lg border border-border bg-card px-3 py-2">

                    <div class="flex items-center gap-2">

                        <i class="fa-solid fa-user-tie text-[11px] text-info"></i>

                        <div>

                            <p class="text-[10px] text-dim">
                                المحصل
                            </p>

                            <p
                                id="result-collector"
                                class="text-xs font-semibold text-fg"
                            >
                                -
                            </p>

                        </div>

                    </div>

                </div>

            </div>


            {{-- ========================================================
                Address
            ========================================================= --}}

            <div class="mt-2 rounded-lg border border-border bg-card px-3 py-2">

                <div class="flex items-start gap-2">

                    <i class="fa-solid fa-location-dot mt-1 text-[11px] text-warning"></i>

                    <div class="min-w-0">

                        <p class="text-[10px] text-dim">
                            عنوان الزيارة
                        </p>

                        <p
                            id="result-visit-address"
                            class="mt-0.5 break-words text-xs font-semibold leading-5 text-fg"
                        >
                            -
                        </p>

                    </div>

                </div>

            </div>

        </div>


        {{-- ============================================================
            OUTCOME
        ============================================================= --}}

        <div class="rounded-xl border border-border bg-panel/40 p-4">

            <div class="mb-3 flex items-center gap-2">

                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-warning/10 text-warning">
                    <i class="fa-solid fa-list-check text-xs"></i>
                </div>

                <div>

                    <h3 class="text-sm font-bold text-fg">
                        نتيجة الزيارة
                    </h3>

                    <p class="text-[10px] text-dim">
                        اختر النتيجة التي تم تسجيلها أثناء الزيارة
                    </p>

                </div>

            </div>

            <x-select-field
                name="outcome"
                label="النتيجة"
                :options="$outcomes"
                placeholder="اختر نتيجة الزيارة..."
            />


            {{-- Outcome helper --}}

            <div
                id="resultOutcomeHint"
                class="mt-3 hidden rounded-xl border border-info/20 bg-info/10 p-3"
            >

                <div class="flex items-start gap-2">

                    <i class="fa-solid fa-circle-info mt-0.5 text-xs text-info"></i>

                    <div>

                        <p class="text-[11px] font-semibold text-fg">
                            ملاحظة
                        </p>

                        <p
                            id="resultOutcomeHintText"
                            class="mt-0.5 text-[10px] leading-5 text-dim"
                        ></p>

                    </div>

                </div>

            </div>

        </div>


        {{-- ============================================================
            NOTES
        ============================================================= --}}

        <div class="rounded-xl border border-border bg-panel/40 p-4">

            <div class="mb-3 flex items-center justify-between gap-3">

                <div class="flex items-center gap-2">

                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-cyan/10 text-cyan">
                        <i class="fa-solid fa-note-sticky text-xs"></i>
                    </div>

                    <div>

                        <h3 class="text-sm font-bold text-fg">
                            ملاحظات الزيارة
                        </h3>

                        <p class="text-[10px] text-dim">
                            أضف تفاصيل مهمة عن نتيجة الزيارة
                        </p>

                    </div>

                </div>

                <span
                    id="resultNotesCounter"
                    class="text-[10px] text-dim"
                >
                    0 حرف
                </span>

            </div>

            <x-textarea-field
                name="notes"
                label=""
                rows="4"
                placeholder="مثال: تم مقابلة العميل، تم الاتفاق على السداد يوم..."
            />

            <div class="mt-2 flex flex-wrap items-center justify-between gap-2">

                <div class="flex items-center gap-2 text-[10px] text-dim">

                    <i class="fa-solid fa-lightbulb text-warning"></i>

                    <span>
                        اكتب معلومات مختصرة وواضحة يمكن الرجوع إليها لاحقاً.
                    </span>

                </div>

                <span
                    id="resultNotesStatus"
                    class="text-[10px] text-dim"
                ></span>

            </div>

        </div>


        {{-- ============================================================
            QUICK CHECK
        ============================================================= --}}

        <div class="rounded-xl border border-border bg-card p-3">

            <div class="flex items-start gap-3">

                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand/10 text-brand">
                    <i class="fa-solid fa-shield-check text-xs"></i>
                </div>

                <div>

                    <p class="text-xs font-bold text-fg">
                        قبل الحفظ
                    </p>

                    <p class="mt-1 text-[11px] leading-5 text-dim">
                        تأكد من اختيار النتيجة الصحيحة وإضافة أي ملاحظات مهمة قبل إغلاق الزيارة.
                    </p>

                </div>

            </div>

        </div>


        {{-- ============================================================
            VALIDATION
        ============================================================= --}}

        <div
            id="resultValidation"
            class="hidden rounded-xl border border-danger/20 bg-danger/10 p-3"
        >

            <div class="flex items-start gap-2">

                <i class="fa-solid fa-triangle-exclamation mt-0.5 text-danger"></i>

                <div>

                    <p class="text-xs font-bold text-fg">
                        يرجى مراجعة البيانات
                    </p>

                    <ul
                        id="resultValidationList"
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

                <i class="fa-solid fa-circle-check text-brand"></i>

                <span>
                    سيتم تحديث حالة الزيارة بعد حفظ النتيجة.
                </span>

            </div>

            <div class="flex gap-2">

                <button
                    type="submit"
                    class="btn btn-primary"
                    id="saveResultButton"
                >
                    <i class="fa-solid fa-check text-xs"></i>
                    حفظ النتيجة
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
    | Elements
    |--------------------------------------------------------------------------
    */

    const resultForm =
        document.getElementById('resultForm');

    const resultModal =
        document.getElementById('resultModal');

    const outcomeSelect =
        resultForm
            ? resultForm.querySelector('select[name="outcome"]')
            : null;

    const notes =
        resultForm
            ? resultForm.querySelector('textarea[name="notes"]')
            : null;

    const notesCounter =
        document.getElementById('resultNotesCounter');

    const notesStatus =
        document.getElementById('resultNotesStatus');

    const outcomeHint =
        document.getElementById('resultOutcomeHint');

    const outcomeHintText =
        document.getElementById('resultOutcomeHintText');

    const validation =
        document.getElementById('resultValidation');

    const validationList =
        document.getElementById('resultValidationList');

    const saveButton =
        document.getElementById('saveResultButton');


    /*
    |--------------------------------------------------------------------------
    | Outcome hints
    |--------------------------------------------------------------------------
    |
    | Frontend only.
    | لا نغير أي value يتم إرسالها للـbackend.
    |
    */

    const outcomeHints = {

        completed:
            'تمت الزيارة بنجاح. أضف في الملاحظات أهم ما تم الاتفاق عليه مع العميل.',

        paid:
            'إذا تم السداد أثناء الزيارة، سجّل تفاصيل السداد المهمة في الملاحظات.',

        promise:
            'إذا تم الحصول على وعد بالسداد، اكتب التفاصيل المهمة مثل الموعد المتفق عليه.',

        refused:
            'وضح في الملاحظات سبب الرفض أو أي معلومات مهمة ذكرها العميل.',

        unavailable:
            'وضح سبب عدم مقابلة العميل، مثل عدم التواجد أو عدم الوصول للعنوان.',

        missed:
            'اكتب سبب فشل الزيارة وأي إجراء مقترح للمتابعة.',

        cancelled:
            'وضح سبب إلغاء الزيارة إذا كان ذلك مهماً للمتابعة.'

    };


    /*
    |--------------------------------------------------------------------------
    | Outcome hint
    |--------------------------------------------------------------------------
    */

    function updateOutcomeHint() {

        if (
            !outcomeSelect ||
            !outcomeHint ||
            !outcomeHintText
        ) {
            return;
        }

        const value =
            outcomeSelect.value;

        if (!value) {

            outcomeHint.classList.add('hidden');

            outcomeHintText.textContent = '';

            return;
        }

        const hint =
            outcomeHints[value];

        if (!hint) {

            outcomeHint.classList.add('hidden');

            outcomeHintText.textContent = '';

            return;
        }

        outcomeHintText.textContent =
            hint;

        outcomeHint.classList.remove('hidden');
    }


    outcomeSelect?.addEventListener(
        'change',
        updateOutcomeHint
    );


    /*
    |--------------------------------------------------------------------------
    | Notes counter
    |--------------------------------------------------------------------------
    */

    function updateNotesCounter() {

        if (
            !notes ||
            !notesCounter
        ) {
            return;
        }

        const length =
            notes.value.length;

        notesCounter.textContent =
            `${length} حرف`;

        if (notesStatus) {

            if (length === 0) {

                notesStatus.textContent =
                    'الملاحظات فارغة';

                notesStatus.className =
                    'text-[10px] text-dim';

            } else if (length < 20) {

                notesStatus.textContent =
                    'يمكن إضافة المزيد من التفاصيل';

                notesStatus.className =
                    'text-[10px] text-warning';

            } else {

                notesStatus.textContent =
                    'الملاحظات جاهزة';

                notesStatus.className =
                    'text-[10px] text-brand';

            }
        }
    }


    notes?.addEventListener(
        'input',
        updateNotesCounter
    );

    updateNotesCounter();


    /*
    |--------------------------------------------------------------------------
    | IMPORTANT
    |--------------------------------------------------------------------------
    |
    | لا نضع هنا:
    |
    | window.openResult = function(...)
    |
    | ولا:
    |
    | document.addEventListener('click', '[data-result]')
    |
    | لأن visits.blade.php الرئيسي هو المسؤول عن:
    |
    | 1. العثور على زر الزيارة.
    | 2. قراءة data-client.
    | 3. قراءة data-client-code.
    | 4. قراءة data-collector.
    | 5. قراءة data-when.
    | 6. قراءة data-address.
    | 7. ضبط form.action.
    | 8. فتح resultModal.
    |
    | وجود openResult هنا كان سبباً محتملاً للتعارض.
    |
    */


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    function validateResult() {

        const errors = [];

        if (
            outcomeSelect &&
            !outcomeSelect.value
        ) {

            errors.push(
                'اختر نتيجة الزيارة.'
            );
        }


        if (
            validation &&
            validationList
        ) {

            validationList.innerHTML = '';

            errors.forEach(error => {

                const li =
                    document.createElement('li');

                li.textContent =
                    error;

                validationList.appendChild(li);

            });

            validation.classList.toggle(
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

    resultForm?.addEventListener(
        'submit',
        event => {

            if (!validateResult()) {

                event.preventDefault();

                return;
            }


            /*
            | Prevent double submit
            */

            if (
                saveButton &&
                saveButton.dataset.submitted === '1'
            ) {

                event.preventDefault();

                return;
            }


            if (saveButton) {

                saveButton.dataset.submitted =
                    '1';

                saveButton.disabled =
                    true;

                saveButton.innerHTML = `
                    <i class="fa-solid fa-spinner fa-spin text-xs"></i>
                    جاري حفظ النتيجة...
                `;
            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Ctrl + Enter
    |--------------------------------------------------------------------------
    */

    resultForm?.addEventListener(
        'keydown',
        event => {

            if (
                event.ctrlKey &&
                event.key === 'Enter'
            ) {

                event.preventDefault();

                resultForm.requestSubmit();

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Reset button state when modal closes
    |--------------------------------------------------------------------------
    */

    resultModal?.addEventListener(
        'close',
        () => {

            if (saveButton) {

                saveButton.dataset.submitted =
                    '0';

                saveButton.disabled =
                    false;

                saveButton.innerHTML = `
                    <i class="fa-solid fa-check text-xs"></i>
                    حفظ النتيجة
                `;
            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Server validation
    |--------------------------------------------------------------------------
    |
    | فتح الـmodal بعد redirect يتم من visits.blade.php الرئيسي.
    |
    | لذلك لا نستدعي openResult() هنا.
    |
    */

});
</script>

@endpush