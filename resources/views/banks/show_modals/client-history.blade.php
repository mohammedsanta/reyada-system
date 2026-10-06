{{-- resources/views/banks/show_modals/client-history.blade.php --}}

<x-modal id="clientHistoryModal" title="سجل العميل" width="58rem">

    <div class="space-y-5">

        <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-line bg-surface p-4">

            <div class="flex items-center gap-3">

                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-info/15 text-info">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </span>

                <div>
                    <h3 class="font-bold text-fg">سجل العميل</h3>
                    <p id="history-client-name" class="mt-1 text-xs text-muted">-</p>
                </div>

            </div>

            <span id="history-client-code" class="badge badge-info font-mono">
                -
            </span>

        </div>


        <div class="rounded-2xl border border-line bg-surface p-5">

            <div class="mb-5 flex items-center justify-between">

                <div>
                    <h3 class="font-bold text-fg">النشاط</h3>
                    <p class="mt-1 text-xs text-dim">
                        جميع الأحداث المرتبطة بالعميل
                    </p>
                </div>

                <span class="badge badge-outline-info">
                    Frontend
                </span>

            </div>


            <div id="client-history-empty" class="py-12 text-center">

                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white/5 text-dim">
                    <i class="fa-solid fa-timeline text-xl"></i>
                </span>

                <h4 class="mt-4 font-semibold text-fg">
                    لا يوجد سجل مسجل حالياً
                </h4>

                <p class="mx-auto mt-2 max-w-md text-xs leading-6 text-muted">
                    واجهة سجل العميل جاهزة للربط ببيانات النشاط من الـ Backend لاحقاً.
                </p>

            </div>

            <div id="client-history-list" class="hidden space-y-3"></div>

        </div>

    </div>

</x-modal>


@push('scripts')
<script>
(function () {

    window.openClientHistory = function (client) {

        if (!client) {
            return;
        }

        const name = document.getElementById('history-client-name');
        const code = document.getElementById('history-client-code');

        if (name) {
            name.textContent = client.name || '-';
        }

        if (code) {
            code.textContent = client.code || '-';
        }

        const modal = document.getElementById('clientHistoryModal');

        if (modal) {
            modal.showModal();
        }

    };

    window.prepareClientModals = function (client) {

        window.activeBankClient = client;

    };

})();
</script>
@endpush