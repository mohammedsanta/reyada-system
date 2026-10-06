{{-- resources/views/banks/show_modals/client-payment.blade.php --}}

<x-modal id="clientPaymentModal" title="تسجيل دفعة" width="40rem">

    <form id="clientPaymentForm" class="space-y-5">

        <div class="rounded-2xl border border-line bg-surface p-4">

            <div class="flex items-center gap-3">

                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand/15 text-brand">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </span>

                <div>

                    <h3 class="font-bold text-fg">
                        تسجيل دفعة
                    </h3>

                    <p id="payment-client-name" class="mt-1 text-xs text-muted">
                        -
                    </p>

                </div>

            </div>

        </div>


        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

            <x-form-field
                name="payment_amount"
                id="payment-amount"
                label="مبلغ الدفع"
                type="number"
                step="0.01"
                required
            />

            <x-form-field
                name="payment_date"
                id="payment-date"
                label="تاريخ الدفع"
                type="date"
                required
            />

            <div>

                <label class="form-label">
                    طريقة الدفع
                </label>

                <select
                    name="payment_method"
                    id="payment-method"
                    class="form-input w-full"
                >
                    <option value="">اختر طريقة الدفع...</option>
                    <option value="cash">نقدي</option>
                    <option value="bank">تحويل بنكي</option>
                    <option value="card">بطاقة</option>
                    <option value="other">أخرى</option>
                </select>

            </div>

            <x-form-field
                name="receipt"
                id="payment-receipt"
                label="رقم الإيصال"
            />

            <div class="sm:col-span-2">

                <x-textarea-field
                    name="payment_note"
                    label="ملاحظات"
                    rows="3"
                    placeholder="ملاحظات عن عملية السداد..."
                />

            </div>

        </div>


        <div class="rounded-xl border border-brand/20 bg-brand/[0.05] px-4 py-3 text-xs text-brand">

            <div class="flex gap-2">

                <i class="fa-solid fa-circle-info mt-0.5"></i>

                <span>
                    هذه الواجهة Frontend فقط حالياً ولن يتم تسجيل الدفعة فعلياً.
                </span>

            </div>

        </div>


        <div class="flex gap-2">

            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-check text-xs"></i>
                تسجيل الدفعة
            </button>

            <button type="button" class="btn btn-secondary" data-close-modal>
                إلغاء
            </button>

        </div>

    </form>

</x-modal>


@push('scripts')
<script>
(function () {

    const form = document.getElementById('clientPaymentForm');

    window.openClientPayment = function (client) {

        if (!client) {
            return;
        }

        const name = document.getElementById('payment-client-name');

        if (name) {
            name.textContent =
                (client.name || '-') +
                ' • ' +
                (client.code || '-');
        }

        const modal = document.getElementById('clientPaymentModal');

        if (modal) {
            modal.showModal();
        }

    };

    if (form) {

        form.addEventListener('submit', function (event) {

            event.preventDefault();

            alert('واجهة تسجيل الدفعة جاهزة. الحفظ سيتم بعد ربط الـ Backend.');

        });

    }

})();
</script>
@endpush