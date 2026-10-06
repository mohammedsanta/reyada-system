{{-- resources/views/banks/_assign-modal.blade.php
     Included at the end of banks/show.blade.php (the clients page).
     Needs $bank.

     Opened by:
     - the cyan "تعيين لموظف" toolbar button  (all ticked rows)
     - the row menu item                      (only that row)
--}}

@php
    // TODO (DB): only the users who are assigned to this bank (bank_user)
    $assignEmployees = \App\Support\StaticData::employees()
        ->mapWithKeys(fn ($user) => [
            $user->id => $user->name . ' (' . $user->role . ')'
        ])
        ->all();
@endphp

<x-modal id="assignModal" title="تعيين لموظف" width="32rem">

    <form id="assignForm"
          method="POST"
          action="{{ route('banks.clients.assign', $bank->id) }}"
          class="space-y-4">

        @csrf

        <input type="hidden" name="_form" value="assign">

        {{-- the selected case ids are added here as hidden inputs: client_ids[] --}}

        <p class="text-sm text-muted">
            سيتم تعيين
            <b id="assign-count" class="text-fg">0</b>
            حالة للموظف الذي تختاره.
        </p>

        @error('client_ids')
            <p class="form-error">{{ $message }}</p>
        @enderror

        {{-- ============================================================
             EMPLOYEE SEARCH
             ============================================================ --}}

        <div class="space-y-2">

            <label for="employee-search" class="form-label">
                البحث عن الموظف
            </label>

            <div class="relative">
                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-dim"></i>

                <input
                    id="employee-search"
                    type="search"
                    autocomplete="off"
                    class="form-input pl-9"
                    placeholder="الاسم، User ID أو Employee Code..."
                    aria-label="البحث عن الموظف"
                >
            </div>

            <p class="text-[11px] text-dim">
                يمكنك البحث بالاسم أو رقم المستخدم أو كود الموظف.
            </p>

            {{-- Search status --}}
            <div id="employee-search-status"
                 class="hidden rounded-lg border border-line bg-surface px-3 py-2 text-xs text-muted">
            </div>

            {{-- Search results --}}
            <div id="employee-search-results"
                 class="hidden max-h-64 space-y-2 overflow-y-auto rounded-xl border border-line bg-surface p-2">
            </div>

        </div>

        {{-- ============================================================
             SELECTED EMPLOYEE
             ============================================================ --}}

        <div id="selected-employee-card"
             class="hidden rounded-xl border border-brand/20 bg-brand/[0.05] p-3">

            <div class="flex items-start justify-between gap-3">

                <div class="flex min-w-0 items-center gap-3">

                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-brand/10 text-brand">
                        <i class="fa-solid fa-user"></i>
                    </span>

                    <div class="min-w-0">

                        <p id="selected-employee-name"
                           class="truncate font-semibold text-fg">
                            -
                        </p>

                        <div class="mt-1 flex flex-wrap items-center gap-2 text-[11px] text-muted">

                            <span>
                                ID:
                                <b id="selected-employee-id"
                                   dir="ltr"
                                   class="text-fg">
                                    -
                                </b>
                            </span>

                            <span class="text-dim">-</span>

                            <span>
                                Code:
                                <b id="selected-employee-code"
                                   dir="ltr"
                                   class="text-info">
                                    -
                                </b>
                            </span>

                        </div>

                    </div>

                </div>

                <button
                    type="button"
                    id="clear-selected-employee"
                    class="icon-btn icon-btn-danger"
                    title="إلغاء اختيار الموظف"
                    aria-label="إلغاء اختيار الموظف">
                    <i class="fa-solid fa-xmark"></i>
                </button>

            </div>

            <div id="selected-employee-meta"
                 class="mt-3 flex flex-wrap gap-2">
            </div>

        </div>

        {{-- ============================================================
             ORIGINAL EMPLOYEE FIELD
             ============================================================ --}}

        <div id="employee-select-wrapper">

            <x-select-field
                name="employee_id"
                label="الموظف"
                :options="$assignEmployees"
                placeholder="اختر الموظف..."
            />

            <p class="mt-1 text-[11px] text-dim">
                يمكنك أيضًا اختيار الموظف مباشرة من القائمة.
            </p>

        </div>

        {{-- ============================================================
             ONLY UNASSIGNED
             ============================================================ --}}

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

        {{-- ============================================================
             REASON
             ============================================================ --}}

        <x-form-field
            name="reason"
            label="سبب التعيين (اختياري)"
            placeholder="مثال: إعادة توزيع بعد إجازة الموظف"
        />

        {{-- ============================================================
             ACTIONS
             ============================================================ --}}

        <div class="flex gap-2">

            <button
                id="assign-submit"
                type="submit"
                class="btn btn-primary">

                <i class="fa-solid fa-user-check text-xs"></i>

                <span data-submit-label>
                    تعيين
                </span>

            </button>

            <button
                type="button"
                class="btn btn-secondary"
                data-close-modal>
                إلغاء
            </button>

        </div>

    </form>

</x-modal>

@push('scripts')
<script>
(function () {

    /*
    |--------------------------------------------------------------------------
    | Elements
    |--------------------------------------------------------------------------
    */

    const dialog = document.getElementById('assignModal');
    const form = document.getElementById('assignForm');
    const counter = document.getElementById('assign-count');

    const searchInput = document.getElementById('employee-search');
    const searchResults = document.getElementById('employee-search-results');
    const searchStatus = document.getElementById('employee-search-status');

    const selectedCard = document.getElementById('selected-employee-card');
    const selectedName = document.getElementById('selected-employee-name');
    const selectedId = document.getElementById('selected-employee-id');
    const selectedCode = document.getElementById('selected-employee-code');
    const selectedMeta = document.getElementById('selected-employee-meta');

    const clearSelectedButton = document.getElementById('clear-selected-employee');

    const employeeSelectWrapper = document.getElementById('employee-select-wrapper');
    const employeeSelect = form?.querySelector('[name="employee_id"]');

    const submitButton = document.getElementById('assign-submit');
    const submitLabel = submitButton?.querySelector('[data-submit-label]');


    /*
    |--------------------------------------------------------------------------
    | State
    |--------------------------------------------------------------------------
    */

    let searchTimer = null;
    let searchController = null;
    let selectedEmployee = null;
    let submitting = false;


    /*
    |--------------------------------------------------------------------------
    | Selected client IDs
    |--------------------------------------------------------------------------
    |
    | Replace the hidden client_ids[] inputs with the given IDs.
    |
    */

    function fillIds(ids) {

        if (!form) {
            return;
        }

        form.querySelectorAll('input[name="client_ids[]"]').forEach(input => {
            input.remove();
        });

        ids = Array.isArray(ids)
            ? [...new Set(ids.map(String))]
            : [];

        ids.forEach(id => {

            const input = document.createElement('input');

            input.type = 'hidden';
            input.name = 'client_ids[]';
            input.value = id;

            form.appendChild(input);
        });

        if (counter) {
            counter.textContent = ids.length;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Selected employee
    |--------------------------------------------------------------------------
    */

    function getEmployeeCode(employee) {

        return employee.employee_code
            ?? employee.code
            ?? employee.employee_id
            ?? '-';
    }


    function clearSelectedEmployee(clearSelect = true) {

        selectedEmployee = null;

        selectedCard?.classList.add('hidden');

        if (selectedName) {
            selectedName.textContent = '-';
        }

        if (selectedId) {
            selectedId.textContent = '-';
        }

        if (selectedCode) {
            selectedCode.textContent = '-';
        }

        if (selectedMeta) {
            selectedMeta.innerHTML = '';
        }

        if (clearSelect && employeeSelect) {
            employeeSelect.value = '';
        }
    }


    function createMetaBadge(label, value, className = 'text-muted') {

        const badge = document.createElement('span');

        badge.className =
            'rounded-md border border-line bg-white/[0.03] px-2 py-1 text-[10px] ' +
            className;

        const labelElement = document.createElement('span');

        labelElement.textContent = label + ': ';

        const valueElement = document.createElement('b');

        valueElement.textContent = value ?? '-';
        valueElement.className = 'text-fg';

        badge.appendChild(labelElement);
        badge.appendChild(valueElement);

        return badge;
    }


    function showSelectedEmployee(employee) {

        selectedEmployee = employee;

        if (!employee) {
            clearSelectedEmployee(false);
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Keep the real employee_id field.
        |--------------------------------------------------------------------------
        */

        if (employeeSelect) {
            employeeSelect.value = String(employee.id ?? '');
        }

        if (selectedName) {
            selectedName.textContent = employee.name ?? '-';
        }

        if (selectedId) {
            selectedId.textContent = employee.id ?? '-';
        }

        if (selectedCode) {
            selectedCode.textContent = getEmployeeCode(employee);
        }

        if (selectedMeta) {

            selectedMeta.innerHTML = '';

            const status = employee.status ?? '-';
            const role = employee.role ?? '-';

            selectedMeta.appendChild(
                createMetaBadge('الحالة', status)
            );

            selectedMeta.appendChild(
                createMetaBadge('الدور', role)
            );

            if (employee.email) {
                selectedMeta.appendChild(
                    createMetaBadge('البريد', employee.email)
                );
            }

            if (employee.phone) {
                selectedMeta.appendChild(
                    createMetaBadge('الهاتف', employee.phone)
                );
            }

            if (employee.supervisor) {
                selectedMeta.appendChild(
                    createMetaBadge('المشرف', employee.supervisor)
                );
            }
        }

        selectedCard?.classList.remove('hidden');

        /*
        |--------------------------------------------------------------------------
        | Hide the long select after choosing from search.
        |--------------------------------------------------------------------------
        |
        | The actual employee_id select remains in the form and still submits.
        |
        */

        employeeSelectWrapper?.classList.add('hidden');

        searchResults?.classList.add('hidden');

        if (searchStatus) {
            searchStatus.classList.add('hidden');
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Search status
    |--------------------------------------------------------------------------
    */

    function setSearchStatus(message, type = 'normal') {

        if (!searchStatus) {
            return;
        }

        searchStatus.textContent = message;

        searchStatus.className =
            'rounded-lg border px-3 py-2 text-xs';

        if (type === 'error') {

            searchStatus.classList.add(
                'border-danger/20',
                'bg-danger/[0.05]',
                'text-danger'
            );

        } else if (type === 'success') {

            searchStatus.classList.add(
                'border-brand/20',
                'bg-brand/[0.05]',
                'text-brand'
            );

        } else {

            searchStatus.classList.add(
                'border-line',
                'bg-surface',
                'text-muted'
            );
        }

        searchStatus.classList.remove('hidden');
    }


    /*
    |--------------------------------------------------------------------------
    | Clear search results
    |--------------------------------------------------------------------------
    */

    function clearSearchResults() {

        if (searchResults) {
            searchResults.innerHTML = '';
            searchResults.classList.add('hidden');
        }

        if (searchStatus) {
            searchStatus.classList.add('hidden');
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Employee result
    |--------------------------------------------------------------------------
    */

    function createEmployeeResult(employee) {

        const button = document.createElement('button');

        button.type = 'button';

        button.className =
            'w-full rounded-xl border border-line bg-white/[0.02] p-3 text-right transition ' +
            'hover:border-brand/30 hover:bg-brand/[0.04]';

        const wrapper = document.createElement('div');

        wrapper.className =
            'flex items-start justify-between gap-3';


        /*
        |--------------------------------------------------------------------------
        | Left side
        |--------------------------------------------------------------------------
        */

        const information = document.createElement('div');

        information.className =
            'min-w-0 flex-1';


        const name = document.createElement('p');

        name.className =
            'truncate font-semibold text-fg';

        name.textContent =
            employee.name ?? '-';


        const identifiers = document.createElement('div');

        identifiers.className =
            'mt-1 flex flex-wrap items-center gap-2 text-[10px] text-muted';


        const id = document.createElement('span');

        id.dir = 'ltr';

        id.textContent =
            'ID: ' + (employee.id ?? '-');


        const separator = document.createElement('span');

        separator.className = 'text-dim';

        separator.textContent = '-';


        const code = document.createElement('span');

        code.dir = 'ltr';

        code.className = 'text-info';

        code.textContent =
            'Code: ' + getEmployeeCode(employee);


        identifiers.appendChild(id);
        identifiers.appendChild(separator);
        identifiers.appendChild(code);


        /*
        |--------------------------------------------------------------------------
        | Meta information
        |--------------------------------------------------------------------------
        */

        const meta = document.createElement('div');

        meta.className =
            'mt-2 flex flex-wrap items-center gap-2';


        const status = document.createElement('span');

        status.className =
            'badge ' +
            (
                String(employee.status ?? '').toUpperCase() === 'ACTIVE'
                    ? 'badge-success'
                    : 'badge-neutral'
            );

        status.textContent =
            employee.status ?? '-';


        meta.appendChild(status);


        if (employee.role) {

            const role = document.createElement('span');

            role.className =
                'badge badge-outline-info';

            role.textContent =
                employee.role;

            meta.appendChild(role);
        }


        information.appendChild(name);
        information.appendChild(identifiers);
        information.appendChild(meta);


        /*
        |--------------------------------------------------------------------------
        | Select button/icon
        |--------------------------------------------------------------------------
        */

        const action = document.createElement('span');

        action.className =
            'flex shrink-0 items-center gap-1 text-xs text-brand';

        action.innerHTML =
            '<i class="fa-solid fa-check text-[10px]"></i> اختيار';


        wrapper.appendChild(information);
        wrapper.appendChild(action);

        button.appendChild(wrapper);


        /*
        |--------------------------------------------------------------------------
        | Click
        |--------------------------------------------------------------------------
        */

        button.addEventListener('click', function () {

            showSelectedEmployee(employee);

            searchInput.value =
                employee.name ?? '';

        });


        return button;
    }


    /*
    |--------------------------------------------------------------------------
    | Render results
    |--------------------------------------------------------------------------
    */

    function renderEmployeeResults(employees, query) {

        if (!searchResults) {
            return;
        }

        searchResults.innerHTML = '';

        if (!employees.length) {

            const empty = document.createElement('div');

            empty.className =
                'py-6 text-center text-xs text-dim';

            empty.innerHTML =
                '<i class="fa-solid fa-user-slash mb-2 block text-lg"></i>' +
                'لا يوجد موظفون مطابقون للبحث.';

            searchResults.appendChild(empty);

            searchResults.classList.remove('hidden');

            setSearchStatus(
                'لم يتم العثور على موظف مطابق.',
                'normal'
            );

            return;
        }


        const header = document.createElement('div');

        header.className =
            'mb-2 px-2 text-[11px] text-muted';

        header.textContent =
            'نتائج البحث: ' + employees.length;

        searchResults.appendChild(header);


        employees.forEach(employee => {

            searchResults.appendChild(
                createEmployeeResult(employee)
            );

        });

        searchResults.classList.remove('hidden');

        setSearchStatus(
            'تم العثور على ' + employees.length + ' موظف.',
            'success'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Search employees
    |--------------------------------------------------------------------------
    */

    async function searchEmployees(query) {

        query = String(query ?? '').trim();

        /*
        |--------------------------------------------------------------------------
        | Don't search empty input.
        |--------------------------------------------------------------------------
        */

        if (!query) {

            clearSearchResults();

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Cancel previous request.
        |--------------------------------------------------------------------------
        |
        | Prevents an older/slower request from overwriting newer results.
        |
        */

        if (searchController) {
            searchController.abort();
        }

        searchController = new AbortController();


        setSearchStatus(
            'جاري البحث...',
            'normal'
        );


        const url =
            @json(route('banks.clients.assign.employees.search', $bank->id))
            + '?q='
            + encodeURIComponent(query);


        try {

            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                signal: searchController.signal
            });


            if (!response.ok) {
                throw new Error('Search request failed');
            }


            const data = await response.json();


            renderEmployeeResults(
                Array.isArray(data.employees)
                    ? data.employees
                    : [],
                query
            );


        } catch (error) {

            /*
            |--------------------------------------------------------------------------
            | Abort is expected when user types another character.
            |--------------------------------------------------------------------------
            */

            if (error.name === 'AbortError') {
                return;
            }


            if (searchResults) {
                searchResults.innerHTML = '';

                const errorElement =
                    document.createElement('div');

                errorElement.className =
                    'py-6 text-center text-xs text-danger';

                errorElement.innerHTML =
                    '<i class="fa-solid fa-circle-exclamation mb-2 block text-lg"></i>' +
                    'حدث خطأ أثناء البحث. حاول مرة أخرى.';

                searchResults.appendChild(errorElement);

                searchResults.classList.remove('hidden');
            }

            setSearchStatus(
                'تعذر تنفيذ البحث.',
                'error'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Search input
    |--------------------------------------------------------------------------
    |
    | Debounce prevents a request on every single keystroke.
    |
    */

    searchInput?.addEventListener('input', function () {

        const query = this.value.trim();

        clearTimeout(searchTimer);

        searchTimer = setTimeout(function () {

            searchEmployees(query);

        }, 300);
    });


    /*
    |--------------------------------------------------------------------------
    | Clear selected employee
    |--------------------------------------------------------------------------
    */

    clearSelectedButton?.addEventListener('click', function () {

        clearSelectedEmployee();

        employeeSelectWrapper?.classList.remove('hidden');

        searchInput.value = '';

        clearSearchResults();

        searchInput?.focus();
    });


    /*
    |--------------------------------------------------------------------------
    | Direct select support
    |--------------------------------------------------------------------------
    |
    | The original select still works.
    |
    */

    employeeSelect?.addEventListener('change', function () {

        const id = String(this.value ?? '');

        if (!id) {

            clearSelectedEmployee(false);

            employeeSelectWrapper?.classList.remove('hidden');

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Find the selected employee from the original static options.
        |--------------------------------------------------------------------------
        |
        | We only need the name here.
        | Search gives us the richer employee object.
        |
        */

        const option =
            this.options[this.selectedIndex];

        const employee = {
            id: id,
            name: option?.textContent?.trim() || '-',
            employee_code: '-',
            status: '-',
            role: '-'
        };

        showSelectedEmployee(employee);
    });


    /*
    |--------------------------------------------------------------------------
    | Open modal
    |--------------------------------------------------------------------------
    */

    document.addEventListener('click', function (event) {

        /*
        |--------------------------------------------------------------------------
        | Toolbar button
        |--------------------------------------------------------------------------
        |
        | The existing show.blade.php selection system remains responsible
        | for selecting the rows.
        |
        */

        const toolbar =
            event.target.closest('[data-open-modal="assignModal"]');

        if (toolbar) {

            const selectedIds = [
                ...document.querySelectorAll('.row-check:checked')
            ].map(box => box.value);

            fillIds(selectedIds);

            /*
            | Reset employee search for every new assignment operation.
            */

            resetAssignEmployeeUI();

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Row menu
        |--------------------------------------------------------------------------
        */

        const one =
            event.target.closest('[data-assign-one]');

        if (one) {

            one.closest('details')?.removeAttribute('open');

            fillIds([
                one.dataset.assignOne
            ]);

            resetAssignEmployeeUI();

            /*
            | The original file explicitly opens the modal here.
            */

            dialog?.showModal();
        }

    });


    /*
    |--------------------------------------------------------------------------
    | Reset employee UI
    |--------------------------------------------------------------------------
    */

    function resetAssignEmployeeUI() {

        clearTimeout(searchTimer);

        if (searchController) {
            searchController.abort();
            searchController = null;
        }

        selectedEmployee = null;

        if (searchInput) {
            searchInput.value = '';
        }

        clearSelectedEmployee();

        employeeSelectWrapper?.classList.remove('hidden');

        clearSearchResults();

        /*
        | Keep old validation value if Laravel sent one back.
        | Otherwise clear it for a fresh assignment.
        */

        @if(!($errors->any() && old('_form') === 'assign'))
            if (employeeSelect) {
                employeeSelect.value = '';
            }
        @endif
    }


    /*
    |--------------------------------------------------------------------------
    | Form submit protection
    |--------------------------------------------------------------------------
    */

    form?.addEventListener('submit', function (event) {

        if (submitting) {

            event.preventDefault();

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Make sure an employee was selected.
        |--------------------------------------------------------------------------
        */

        if (!employeeSelect?.value) {

            event.preventDefault();

            setSearchStatus(
                'اختر موظفًا قبل تنفيذ التعيين.',
                'error'
            );

            searchInput?.focus();

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Make sure there are selected cases.
        |--------------------------------------------------------------------------
        */

        const selectedIds =
            form.querySelectorAll('input[name="client_ids[]"]');


        if (!selectedIds.length) {

            event.preventDefault();

            setSearchStatus(
                'اختر حالة واحدة على الأقل.',
                'error'
            );

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Prevent double submit.
        |--------------------------------------------------------------------------
        */

        submitting = true;

        if (submitButton) {

            submitButton.disabled = true;

            submitButton.classList.add(
                'opacity-70',
                'cursor-wait'
            );
        }

        if (submitLabel) {
            submitLabel.textContent = 'جاري التعيين...';
        }

    });


    /*
    |--------------------------------------------------------------------------
    | Keyboard shortcuts
    |--------------------------------------------------------------------------
    */

    dialog?.addEventListener('keydown', function (event) {

        /*
        | Ctrl + K => focus employee search
        */

        if (
            event.ctrlKey &&
            event.key.toLowerCase() === 'k'
        ) {

            event.preventDefault();

            searchInput?.focus();

            searchInput?.select();

            return;
        }


        /*
        | Escape is handled naturally by the dialog/modal.
        */
    });


    /*
    |--------------------------------------------------------------------------
    | Validation failed
    |--------------------------------------------------------------------------
    |
    | Re-open the dialog with the same selected cases.
    |
    */

    @if($errors->any() && old('_form') === 'assign')

        fillIds(
            @json(
                array_map(
                    'strval',
                    (array) old('client_ids', [])
                )
            )
        );

        dialog?.showModal();

        /*
        |--------------------------------------------------------------------------
        | Restore old employee selection.
        |--------------------------------------------------------------------------
        */

        if (employeeSelect) {
            employeeSelect.value =
                @json((string) old('employee_id', ''));
        }

        /*
        |--------------------------------------------------------------------------
        | If Laravel returned an employee ID,
        | search it automatically so the selected employee card
        | can be restored.
        */

        const oldEmployeeId =
            @json((string) old('employee_id', ''));

        if (oldEmployeeId) {

            searchEmployees(oldEmployeeId);

        }

    @endif


    /*
    |--------------------------------------------------------------------------
    | Initial counter
    |--------------------------------------------------------------------------
    */

    if (counter) {

        counter.textContent =
            form?.querySelectorAll(
                'input[name="client_ids[]"]'
            ).length ?? 0;
    }

})();
</script>
@endpush