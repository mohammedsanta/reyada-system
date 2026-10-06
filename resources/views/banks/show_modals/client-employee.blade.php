{{-- resources/views/banks/show_modals/client-employee.blade.php --}}

<x-modal id="clientEmployeeModal" title="الموظف المسؤول" width="42rem">

    <div class="space-y-5">

        <div class="rounded-2xl border border-line bg-surface p-5">

            <div class="flex items-center gap-4">

                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-info/15 text-info">
                    <i class="fa-solid fa-user-tie text-xl"></i>
                </span>

                <div class="min-w-0">

                    <p class="text-xs text-muted">
                        الموظف الحالي
                    </p>

                    <h3 id="employee-client-current" class="mt-1 text-lg font-bold text-fg">
                        غير مسند
                    </h3>

                    <p id="employee-client-code" class="mt-1 font-mono text-xs text-dim">
                        -
                    </p>

                </div>

            </div>

        </div>


        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

            <button
                type="button"
                id="client-change-employee-btn"
                class="group rounded-2xl border border-info/20 bg-info/[0.04] p-5 text-right transition hover:border-info/40 hover:bg-info/[0.08]"
            >

                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-info/15 text-info">
                    <i class="fa-solid fa-user-pen"></i>
                </span>

                <h4 class="mt-4 font-bold text-fg">
                    تغيير الموظف
                </h4>

                <p class="mt-1 text-xs leading-5 text-muted">
                    اختيار موظف آخر للحالة.
                </p>

            </button>


            <button
                type="button"
                id="client-unassign-btn"
                class="group rounded-2xl border border-danger/20 bg-danger/[0.04] p-5 text-right transition hover:border-danger/40 hover:bg-danger/[0.08]"
            >

                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-danger/15 text-danger">
                    <i class="fa-solid fa-user-minus"></i>
                </span>

                <h4 class="mt-4 font-bold text-fg">
                    إلغاء التعيين
                </h4>

                <p class="mt-1 text-xs leading-5 text-muted">
                    إزالة الموظف الحالي من الحالة.
                </p>

            </button>

        </div>


        <div class="rounded-xl border border-warning/20 bg-warning/[0.05] px-4 py-3 text-xs text-warning">

            <div class="flex gap-2">

                <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>

                <span>
                    تغيير الموظف وإلغاء التعيين في هذه المرحلة واجهة Frontend فقط.
                    لن يتم تعديل البيانات الفعلية حتى يتم ربط الـ Backend.
                </span>

            </div>

        </div>


        <div class="flex justify-end">

            <button
                type="button"
                class="btn btn-secondary"
                data-close-modal
            >
                إغلاق
            </button>

        </div>

    </div>

</x-modal>


@push('scripts')
<script>
(function () {

    let activeClient = null;

    window.openClientEmployee = function (client) {

        if (!client) {
            return;
        }

        activeClient = client;

        const current =
            client.employee ||
            client.employee_name ||
            'غير مسند';

        const currentElement =
            document.getElementById('employee-client-current');

        const codeElement =
            document.getElementById('employee-client-code');

        if (currentElement) {
            currentElement.textContent = current;
        }

        if (codeElement) {
            codeElement.textContent = client.code || '-';
        }

        const modal =
            document.getElementById('clientEmployeeModal');

        if (modal) {
            modal.showModal();
        }

    };


    const changeButton =
        document.getElementById('client-change-employee-btn');

    const unassignButton =
        document.getElementById('client-unassign-btn');


    if (changeButton) {

        changeButton.addEventListener('click', function () {

            if (!activeClient) {
                return;
            }

            const assignModal =
                document.getElementById('assignModal');

            if (assignModal) {

                const closeButton =
                    document.querySelector(
                        '#clientEmployeeModal [data-close-modal]'
                    );

                if (closeButton) {
                    closeButton.click();
                }

                assignModal.showModal();

                if (typeof window.fillAssignClientIds === 'function') {
                    window.fillAssignClientIds([activeClient.id]);
                }

            } else {

                alert('واجهة التعيين غير متاحة حالياً.');

            }

        });

    }


    if (unassignButton) {

        unassignButton.addEventListener('click', function () {

            if (!activeClient) {
                return;
            }

            alert(
                'واجهة إلغاء التعيين جاهزة. لن يتم تعديل الموظف فعلياً حتى يتم ربط الـ Backend.'
            );

        });

    }

})();
</script>
@endpush