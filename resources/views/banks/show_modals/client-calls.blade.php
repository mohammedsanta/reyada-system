{{-- resources/views/banks/show_modals/client-calls.blade.php --}}

<x-modal id="clientCallsModal" title="سجل الاتصالات" width="62rem">

    <div class="space-y-5">

        <div class="rounded-2xl border border-line bg-surface p-5">

            <div class="flex flex-wrap items-center justify-between gap-3">

                <div class="flex items-center gap-3">

                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-info/15 text-info">
                        <i class="fa-solid fa-phone-volume"></i>
                    </span>

                    <div>
                        <h3 class="font-bold text-fg">
                            سجل الاتصالات
                        </h3>

                        <p id="calls-client-name" class="mt-1 text-xs text-muted">
                            -
                        </p>
                    </div>

                </div>

                <span id="calls-client-code" class="badge badge-info font-mono">
                    -
                </span>

            </div>

        </div>


        <div class="rounded-2xl border border-line bg-surface p-5">

            <div class="table-wrap">

                <table class="data-table whitespace-nowrap">

                    <thead>

                        <tr>
                            <th>التاريخ</th>
                            <th>الموظف</th>
                            <th>نوع الاتصال</th>
                            <th>النتيجة</th>
                            <th>ملاحظات</th>
                        </tr>

                    </thead>

                    <tbody>

                        <tr>

                            <td colspan="5">

                                <div class="py-10 text-center">

                                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-white/5 text-dim">
                                        <i class="fa-solid fa-phone-slash text-lg"></i>
                                    </span>

                                    <h4 class="mt-4 font-semibold text-fg">
                                        لا توجد اتصالات مسجلة
                                    </h4>

                                    <p class="mx-auto mt-2 max-w-md text-xs leading-6 text-muted">
                                        سجل الاتصالات سيظهر هنا بعد إضافة بيانات المكالمات من النظام.
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

    window.openClientCalls = function (client) {

        if (!client) {
            return;
        }

        const name = document.getElementById('calls-client-name');
        const code = document.getElementById('calls-client-code');

        if (name) {
            name.textContent = client.name || '-';
        }

        if (code) {
            code.textContent = client.code || '-';
        }

        const modal = document.getElementById('clientCallsModal');

        if (modal) {
            modal.showModal();
        }

    };

})();
</script>
@endpush