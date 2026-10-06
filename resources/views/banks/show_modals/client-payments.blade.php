{{-- resources/views/banks/show_modals/client-payments.blade.php --}}

<x-modal id="clientPaymentsModal" title="سجل المدفوعات" width="64rem">

    <div class="space-y-5">

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">

            <x-stat-card
                label="إجمالي المدفوع"
                value="0"
                unit="EGP"
            />

            <x-stat-card
                label="عدد المدفوعات"
                value="0"
                unit="دفعة"
            />

            <x-stat-card
                label="آخر دفعة"
                value="-"
                unit=""
            />

        </div>


        <div class="rounded-2xl border border-line bg-surface p-5">

            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">

                <div>
                    <h3 class="font-bold text-fg">
                        سجل المدفوعات
                    </h3>

                    <p id="payments-client-name" class="mt-1 text-xs text-muted">
                        -
                    </p>
                </div>

                <span id="payments-client-code" class="badge badge-info font-mono">
                    -
                </span>

            </div>


            <div class="table-wrap">

                <table class="data-table whitespace-nowrap">

                    <thead>
                        <tr>
                            <th>رقم الإيصال</th>
                            <th>المبلغ</th>
                            <th>طريقة الدفع</th>
                            <th>تاريخ الدفع</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>

                    <tbody>

                        <tr>
                            <td colspan="5">

                                <div class="py-10 text-center">

                                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-white/5 text-dim">
                                        <i class="fa-solid fa-receipt text-lg"></i>
                                    </span>

                                    <h4 class="mt-4 font-semibold text-fg">
                                        لا توجد مدفوعات مسجلة
                                    </h4>

                                    <p class="mt-2 text-xs text-muted">
                                        سيتم عرض سجل المدفوعات هنا بعد ربط النظام بالـ Backend.
                                    </p>

                                </div>

                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</x-modal>


@push('scripts')
<script>
(function () {

    window.openClientPayments = function (client) {

        if (!client) {
            return;
        }

        const name = document.getElementById('payments-client-name');
        const code = document.getElementById('payments-client-code');

        if (name) {
            name.textContent = client.name || '-';
        }

        if (code) {
            code.textContent = client.code || '-';
        }

        const modal = document.getElementById('clientPaymentsModal');

        if (modal) {
            modal.showModal();
        }

    };

})();
</script>
@endpush