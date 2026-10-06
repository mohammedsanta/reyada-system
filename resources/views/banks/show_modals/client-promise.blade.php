{{-- resources/views/banks/show_modals/client-promise.blade.php --}}

<x-modal id="clientPromiseModal" title="إضافة وعد بالسداد" width="38rem">

    <form id="clientPromiseForm" class="space-y-5">

        <div class="rounded-2xl border border-line bg-surface p-4">

            <div class="flex items-center gap-3">

                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-warning/15 text-warning">
                    <i class="fa-solid fa-handshake"></i>
                </span>

                <div>

                    <h3 class="font-bold text-fg">
                        وعد بالسداد
                    </h3>

                    <p id="promise-client-name" class="mt-1 text-xs text-muted">
                        -
                    </p>

                </div>

            </div>

        </div>


        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

            <x-form-field
                name="promise_date"
                id="promise-date"
                label="تاريخ الوعد"
                type="date"
                required
            />

            <x-form-field
                name="promise_amount"
                id="promise-amount"
                label="المبلغ المتوقع"
                type="number"
                step="0.01"
                required
            />

            <div class="sm:col-span-2">

                <x-textarea-field
                    name="promise_note"
                    label="ملاحظات"
                    rows="4"
                    placeholder="اكتب تفاصيل وعد السداد..."
                />

            </div>

        </div>


        <div class="rounded-xl border border-warning/20 bg-warning/[0.05] px-4 py-3 text-xs text-warning">

            <div class="flex gap-2">

                <i class="fa-solid fa-circle-info mt-0.5"></i>

                <span>
                    هذه الواجهة Frontend فقط حالياً. لن يتم حفظ الوعد في قاعدة البيانات حتى يتم ربط الـ Backend.
                </span>

            </div>

        </div>


        <div class="flex gap-2">

            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-plus text-xs"></i>
                إضافة الوعد
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

    const form = document.getElementById('clientPromiseForm');

    window.openClientPromise = function (client) {

        if (!client) {
            return;
        }

        const name = document.getElementById('promise-client-name');

        if (name) {
            name.textContent =
                (client.name || '-') +
                ' • ' +
                (client.code || '-');
        }

        const modal = document.getElementById('clientPromiseModal');

        if (modal) {
            modal.showModal();
        }

    };

    if (form) {

        form.addEventListener('submit', function (event) {

            event.preventDefault();

            alert('واجهة إضافة وعد بالسداد جاهزة. الحفظ سيتم بعد ربط الـ Backend.');

        });

    }

})();
</script>
@endpush