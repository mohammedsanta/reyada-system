{{-- resources/views/banks/_assign-modal.blade.php
     Included at the end of banks/show.blade.php (the clients page). Needs $bank.
     Opened by:  - the cyan "تعيين لموظف" toolbar button  (all ticked rows)
                 - the row menu item                      (only that row) --}}
@php
    // TODO (DB): only the users who are assigned to this bank (bank_user)
    $assignEmployees = \App\Support\StaticData::employees()
        ->mapWithKeys(fn ($user) => [$user->id => $user->name . ' (' . $user->role . ')'])
        ->all();
@endphp

<x-modal id="assignModal" title="تعيين لموظف" width="32rem">
    <form id="assignForm" method="POST" action="{{ route('banks.clients.assign', $bank->id) }}" class="space-y-4">
        @csrf
        <input type="hidden" name="_form" value="assign">
        {{-- the selected case ids are added here as hidden inputs: client_ids[] --}}

        <p class="text-sm text-muted">
            سيتم تعيين <b id="assign-count" class="text-fg">0</b> حالة للموظف الذي تختاره.
        </p>
        @error('client_ids') <p class="form-error">{{ $message }}</p> @enderror

        <x-select-field name="employee_id" label="الموظف" :options="$assignEmployees" placeholder="اختر الموظف..." />

        <label class="check-row">
            <input type="checkbox" name="only_unassigned" value="1" class="accent-brand" @checked(old('only_unassigned'))>
            <span>
                <b class="text-fg">الحالات غير المسندة فقط</b>
                <span class="block text-[11px] text-dim">لا يتم سحب الحالات التي لها موظف حالياً</span>
            </span>
        </label>

        <x-form-field name="reason" label="سبب التعيين (اختياري)" placeholder="مثال: إعادة توزيع بعد إجازة الموظف" />

        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-user-check text-xs"></i> تعيين</button>
            <button type="button" class="btn btn-secondary" data-close-modal>إلغاء</button>
        </div>
    </form>
</x-modal>

@push('scripts')
    <script>
        (function () {
            const dialog  = document.getElementById('assignModal');
            const form    = document.getElementById('assignForm');
            const counter = document.getElementById('assign-count');

            // replace the hidden client_ids[] inputs with the given ids
            function fillIds(ids) {
                form.querySelectorAll('input[name="client_ids[]"]').forEach(input => input.remove());

                ids.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden'; input.name = 'client_ids[]'; input.value = id;
                    form.appendChild(input);
                });

                counter.textContent = ids.length;
            }

            document.addEventListener('click', (event) => {
                // toolbar button: every ticked row (resources/js/app.js opens the dialog itself)
                if (event.target.closest('[data-open-modal="assignModal"]')) {
                    fillIds([...document.querySelectorAll('.row-check:checked')].map(box => box.value));
                    return;
                }

                // row menu: only this row
                const one = event.target.closest('[data-assign-one]');
                if (one) {
                    one.closest('details')?.removeAttribute('open');
                    fillIds([one.dataset.assignOne]);
                    dialog.showModal();
                }
            });

            // validation failed: open the dialog again with the same selection and the errors
            @if($errors->any() && old('_form') === 'assign')
                fillIds(@json(array_map('strval', (array) old('client_ids', []))));
                dialog.showModal();
            @endif
        })();
    </script>
@endpush