{{-- resources/views/banks/show_modals/client-assign.blade.php --}}

@php
    $assignEmployees = \App\Support\StaticData::employees()
        ->mapWithKeys(fn ($user) => [
            $user->id => $user->name . ' (' . $user->role . ')'
        ])
        ->all();
@endphp


<x-modal id="assignModal" title="تعيين لموظف" width="32rem">

    <form
        id="assignForm"
        method="POST"
        action="{{ route('banks.clients.assign', $bank->id) }}"
        class="space-y-4"
    >

        @csrf

        <input
            type="hidden"
            name="_form"
            value="assign"
        >


        <div class="rounded-xl border border-info/20 bg-info/[0.04] px-4 py-3">

            <div class="flex items-start gap-3">

                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-info/15 text-info">
                    <i class="fa-solid fa-user-plus"></i>
                </span>

                <div>

                    <p class="text-sm text-muted">
                        سيتم تعيين
                        <b id="assign-count" class="text-fg">0</b>
                        حالة للموظف الذي تختاره.
                    </p>

                </div>

            </div>

        </div>


        @error('client_ids')
            <p class="form-error">
                {{ $message }}
            </p>
        @enderror


        <x-select-field
            name="employee_id"
            label="الموظف"
            :options="$assignEmployees"
            placeholder="اختر الموظف..."
        />


        <label class="check-row">

            <input
                type="checkbox"
                name="only_unassigned"
                value="1"
                class="accent-brand"
                @checked(old('only_unassigned'))
            >

            <span>

                <b class="text-fg">
                    الحالات غير المسندة فقط
                </b>

                <span class="block text-[11px] text-dim">
                    لا يتم سحب الحالات التي لها موظف حالياً
                </span>

            </span>

        </label>


        <x-form-field
            name="reason"
            label="سبب التعيين (اختياري)"
            placeholder="مثال: إعادة توزيع بعد إجازة الموظف"
        />


        <div class="flex gap-2">

            <button
                type="submit"
                class="btn btn-primary"
            >
                <i class="fa-solid fa-user-check text-xs"></i>
                تعيين
            </button>

            <button
                type="button"
                class="btn btn-secondary"
                data-close-modal
            >
                إلغاء
            </button>

        </div>

    </form>

</x-modal>


@push('scripts')
<script>
(function () {

    const dialog =
        document.getElementById('assignModal');

    const form =
        document.getElementById('assignForm');

    const counter =
        document.getElementById('assign-count');


    function fillIds(ids) {

        if (!form) {
            return;
        }

        form
            .querySelectorAll('input[name="client_ids[]"]')
            .forEach(function (input) {
                input.remove();
            });


        ids.forEach(function (id) {

            const input =
                document.createElement('input');

            input.type = 'hidden';
            input.name = 'client_ids[]';
            input.value = id;

            form.appendChild(input);

        });


        if (counter) {
            counter.textContent = ids.length;
        }

    }


    window.fillAssignClientIds = fillIds;


    document.addEventListener('click', function (event) {

        const bulk =
            event.target.closest(
                '[data-open-modal="assignModal"]'
            );

        if (bulk) {

            const ids = [
                ...document.querySelectorAll(
                    '.row-check:checked'
                )
            ].map(function (box) {
                return box.value;
            });

            fillIds(ids);

            return;
        }


        const one =
            event.target.closest('[data-assign-one]');

        if (one) {

            one.closest('details')?.removeAttribute('open');

            fillIds([
                one.dataset.assignOne
            ]);

            if (dialog) {
                dialog.showModal();
            }

        }

    });


    @if($errors->any() && old('_form') === 'assign')

        fillIds(
            @json(
                array_map(
                    'strval',
                    (array) old('client_ids', [])
                )
            )
        );

        if (dialog) {
            dialog.showModal();
        }

    @endif

})();
</script>
@endpush