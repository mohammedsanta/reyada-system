{{-- resources/views/banks/show_modals/client.blade.php --}}

<x-modal id="clientModal" title="بيانات العميل" width="72rem">

    <form
        id="clientForm"
        method="POST"
        action="#"
        class="space-y-5"
    >
        @csrf
        @method('PUT')

        <input type="hidden" name="_client_id" id="client-id">

        {{-- =========================================================
             HEADER
        ========================================================== --}}
        <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-line bg-surface p-4">

            <div class="flex min-w-0 items-center gap-3">

                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-info/15 text-info">
                    <i class="fa-solid fa-user text-lg"></i>
                </span>

                <div class="min-w-0">

                    <div class="flex flex-wrap items-center gap-2">

                        <input
                            type="text"
                            name="name"
                            id="client-name"
                            class="min-w-[220px] border-0 bg-transparent p-0 text-lg font-bold text-fg outline-none focus:ring-0"
                            placeholder="اسم العميل"
                        >

                        <span id="client-status-badge" class="badge badge-outline-info">
                            -
                        </span>

                    </div>

                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-muted">

                        <span>
                            National ID:
                            <span id="client-national-summary" class="font-mono text-fg">-</span>
                        </span>

                        <span class="text-dim">•</span>

                        <span>
                            Code:
                            <span id="client-code-summary" class="font-mono text-fg">-</span>
                        </span>

                    </div>

                </div>
            </div>

            <div class="flex items-center gap-2">

                <button
                    type="submit"
                    id="client-save-btn"
                    class="btn btn-primary btn-sm"
                >
                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                    حفظ التعديلات
                </button>

                <button
                    type="button"
                    class="btn btn-secondary btn-sm"
                    data-close-modal
                >
                    <i class="fa-solid fa-xmark text-xs"></i>
                    إغلاق
                </button>

            </div>

        </div>


        {{-- =========================================================
             MAIN GRID
        ========================================================== --}}
        <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">

            {{-- =====================================================
                 LEFT COLUMN
            ====================================================== --}}
            <div class="space-y-5">

                {{-- PERSONAL --}}
                <section class="rounded-2xl border border-line bg-surface p-5">

                    <div class="mb-5 flex items-center gap-3">

                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-info/15 text-info">
                            <i class="fa-solid fa-id-card"></i>
                        </span>

                        <div>
                            <h3 class="font-bold text-fg">
                                البيانات الشخصية والاتصال
                            </h3>

                            <p class="mt-1 text-[11px] text-dim">
                                بيانات العميل الأساسية ووسائل التواصل
                            </p>
                        </div>

                    </div>


                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

                        <x-form-field
                            name="code"
                            id="client-code"
                            label="كود العميل"
                            readonly
                        />

                        <x-form-field
                            name="national_id"
                            id="client-national-id"
                            label="National ID"
                        />

                        <div>
                            <label class="form-label">نوع القرض</label>

                            <select
                                name="loan_type"
                                id="client-loan-type"
                                class="form-input w-full"
                            >
                                <option value="">اختر نوع القرض...</option>
                                <option value="personal">شخصي</option>
                                <option value="car">سيارة</option>
                                <option value="consumer">استهلاكي</option>
                                <option value="credit_card">بطاقة ائتمان</option>
                                <option value="other">أخرى</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">الحالة</label>

                            <select
                                name="status"
                                id="client-status"
                                class="form-input w-full"
                            >
                                <option value="">اختر الحالة...</option>
                                <option value="active">نشط</option>
                                <option value="suspended">موقوف</option>
                                <option value="closed">مغلق</option>
                            </select>
                        </div>

                        <x-form-field
                            name="phone"
                            id="client-phone"
                            label="الهاتف"
                        />

                        <x-form-field
                            name="phone2"
                            id="client-phone2"
                            label="الهاتف الثاني"
                        />

                        <x-form-field
                            name="email"
                            id="client-email"
                            label="البريد الإلكتروني"
                            type="email"
                        />

                        <x-form-field
                            name="governorate"
                            id="client-governorate"
                            label="المحافظة"
                        />

                        <div class="sm:col-span-2">
                            <x-form-field
                                name="address"
                                id="client-address"
                                label="العنوان"
                            />
                        </div>

                    </div>

                </section>


                {{-- DATES / PAYMENTS --}}
                <section class="rounded-2xl border border-line bg-surface p-5">

                    <div class="mb-5 flex items-center gap-3">

                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand/15 text-brand">
                            <i class="fa-solid fa-calendar-days"></i>
                        </span>

                        <div>
                            <h3 class="font-bold text-fg">
                                التواريخ ومدفوعات العميل
                            </h3>

                            <p class="mt-1 text-[11px] text-dim">
                                تواريخ القرض وآخر عملية سداد
                            </p>
                        </div>

                    </div>


                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

                        <x-form-field
                            name="loan_start_date"
                            id="client-loan-start-date"
                            label="تاريخ بداية القرض"
                            type="date"
                        />

                        <x-form-field
                            name="loan_end_date"
                            id="client-loan-end-date"
                            label="تاريخ نهاية القرض"
                            type="date"
                        />

                        <x-form-field
                            name="last_payment_date"
                            id="client-last-payment-date"
                            label="تاريخ آخر دفعة"
                            type="date"
                        />

                        <x-form-field
                            name="last_payment_amount"
                            id="client-last-payment-amount"
                            label="مبلغ آخر دفعة"
                            type="number"
                            step="0.01"
                        />

                    </div>

                </section>

            </div>


            {{-- =====================================================
                 RIGHT COLUMN
            ====================================================== --}}
            <div class="space-y-5">

                {{-- DEBT --}}
                <section class="rounded-2xl border border-line bg-surface p-5">

                    <div class="mb-5 flex items-center gap-3">

                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-warning/15 text-warning">
                            <i class="fa-solid fa-money-bill-trend-up"></i>
                        </span>

                        <div>
                            <h3 class="font-bold text-fg">
                                تفاصيل المديونية
                            </h3>

                            <p class="mt-1 text-[11px] text-dim">
                                تفاصيل المديونية والتأخر
                            </p>
                        </div>

                    </div>


                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

                        <x-form-field
                            name="total_debt"
                            id="client-total-debt"
                            label="إجمالي المديونية"
                            type="number"
                            step="0.01"
                        />

                        <x-form-field
                            name="overdue_amount"
                            id="client-overdue-amount"
                            label="المبلغ المتأخر"
                            type="number"
                            step="0.01"
                        />

                        <x-form-field
                            name="installment_value"
                            id="client-installment-value"
                            label="قيمة القسط"
                            type="number"
                            step="0.01"
                        />

                        <x-form-field
                            name="bucket"
                            id="client-bucket"
                            label="الشريحة BUCKET"
                        />

                        <x-form-field
                            name="dpd"
                            id="client-dpd"
                            label="DPD"
                            type="number"
                        />

                        <x-form-field
                            name="min_installment_diff"
                            id="client-min-installment-diff"
                            label="فرق الحد الأدنى للقسط"
                            type="number"
                            step="0.01"
                        />

                        <x-form-field
                            name="next_due_date"
                            id="client-next-due-date"
                            label="موعد الاستحقاق القادم"
                            type="date"
                        />

                        <x-form-field
                            name="late_fee"
                            id="client-late-fee"
                            label="غرامة التأخر"
                            type="number"
                            step="0.01"
                        />

                    </div>

                </section>


                {{-- WORK --}}
                <section class="rounded-2xl border border-line bg-surface p-5">

                    <div class="mb-5 flex items-center gap-3">

                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-info/15 text-info">
                            <i class="fa-solid fa-briefcase"></i>
                        </span>

                        <div>
                            <h3 class="font-bold text-fg">
                                بيانات العمل
                            </h3>

                            <p class="mt-1 text-[11px] text-dim">
                                بيانات جهة عمل العميل
                            </p>
                        </div>

                    </div>


                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

                        <x-form-field
                            name="employer"
                            id="client-employer"
                            label="جهة العمل"
                        />

                        <x-form-field
                            name="job_title"
                            id="client-job-title"
                            label="الوظيفة"
                        />

                        <x-form-field
                            name="work_phone"
                            id="client-work-phone"
                            label="هاتف العمل"
                        />

                        <div class="sm:col-span-2">
                            <x-form-field
                                name="work_address"
                                id="client-work-address"
                                label="عنوان العمل"
                            />
                        </div>

                    </div>

                </section>

            </div>

        </div>

    </form>

</x-modal>


@push('scripts')
<script>
(function () {

    const modal = document.getElementById('clientModal');
    const form = document.getElementById('clientForm');

    if (!modal || !form) {
        return;
    }

    const clients = @json($clientsJson ?? []);

    window.bankClients = clients;

    window.getBankClient = function (id) {

        return clients.find(function (client) {
            return String(client.id) === String(id);
        }) || null;

    };

    function value(data, key) {

        if (!data) {
            return '';
        }

        return data[key] ?? '';

    }

    function setValue(id, value) {

        const element = document.getElementById(id);

        if (!element) {
            return;
        }

        element.value = value ?? '';

    }

    function setText(id, value) {

        const element = document.getElementById(id);

        if (!element) {
            return;
        }

        element.textContent = value ?? '-';

    }

    function statusLabel(status) {

        const labels = {
            active: 'نشط',
            suspended: 'موقوف',
            closed: 'مغلق',
        };

        return labels[status] || status || '-';

    }

    function fillClient(client) {

        if (!client) {
            return;
        }

        setValue('client-id', value(client, 'id'));

        setValue('client-name', value(client, 'name'));
        setValue('client-code', value(client, 'code'));
        setValue('client-national-id', value(client, 'national_id'));
        setValue('client-loan-type', value(client, 'loan_type'));
        setValue('client-status', value(client, 'status'));

        setValue('client-phone', value(client, 'phone'));
        setValue('client-phone2', value(client, 'phone2'));
        setValue('client-email', value(client, 'email'));
        setValue('client-governorate', value(client, 'governorate'));
        setValue('client-address', value(client, 'address'));

        setValue('client-loan-start-date', value(client, 'loan_start_date'));
        setValue('client-loan-end-date', value(client, 'loan_end_date'));
        setValue('client-last-payment-date', value(client, 'last_payment_date'));
        setValue('client-last-payment-amount', value(client, 'last_payment_amount'));

        setValue('client-total-debt', value(client, 'total_debt'));
        setValue('client-overdue-amount', value(client, 'overdue_amount'));
        setValue('client-installment-value', value(client, 'installment_value'));
        setValue('client-bucket', value(client, 'bucket'));
        setValue('client-dpd', value(client, 'dpd'));
        setValue('client-min-installment-diff', value(client, 'min_installment_diff'));
        setValue('client-next-due-date', value(client, 'next_due_date'));
        setValue('client-late-fee', value(client, 'late_fee'));

        setValue('client-employer', value(client, 'employer'));
        setValue('client-job-title', value(client, 'job_title'));
        setValue('client-work-phone', value(client, 'work_phone'));
        setValue('client-work-address', value(client, 'work_address'));

        setText('client-national-summary', value(client, 'national_id'));
        setText('client-code-summary', value(client, 'code'));

        const statusBadge = document.getElementById('client-status-badge');

        if (statusBadge) {
            statusBadge.textContent = statusLabel(value(client, 'status'));
        }

        form.action = "{{ route('banks.clients.update', [$bank->id, '__CLIENT__']) }}"
            .replace('__CLIENT__', encodeURIComponent(value(client, 'id')));

    }

    window.openBankClient = function (id) {

        const client = window.getBankClient(id);

        if (!client) {
            return;
        }

        window.activeBankClient = client;

        fillClient(client);

        if (typeof window.prepareClientModals === 'function') {
            window.prepareClientModals(client);
        }

        modal.showModal();

    };

    document.addEventListener('click', function (event) {

        const button = event.target.closest('[data-open-client]');

        if (!button) {
            return;
        }

        event.preventDefault();

        window.openBankClient(button.dataset.openClient);

    });

    form.addEventListener('submit', function () {

        const button = document.getElementById('client-save-btn');

        if (!button) {
            return;
        }

        button.disabled = true;

        button.innerHTML =
            '<i class="fa-solid fa-spinner fa-spin text-xs"></i> جاري الحفظ...';

    });

})();
</script>
@endpush