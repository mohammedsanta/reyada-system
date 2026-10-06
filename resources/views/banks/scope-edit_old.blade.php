{{-- resources/views/banks/scope-edit.blade.php --}}
@extends('layouts.app')

@section('title', 'تعديل النطاق - ' . $bank->name)

@section('content')

    <x-bank-header :bank="$bank" title="تعديل النطاق" subtitle="تعديل جماعي لحالات النطاق الحالي" />

    <x-flash />

    @error('field') <div class="alert alert-danger mb-6">{{ $message }}</div> @enderror

    <div class="mb-6 flex items-center gap-3 rounded-xl border border-warning/20 bg-warning/[0.05] px-4 py-3 text-sm text-warning">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <span>التعديل الجماعي يغيّر حقلاً واحداً في كل الحالات المطابقة للفلاتر، ويُسجَّل في سجل النشاط. راجع المعاينة قبل التنفيذ.</span>
    </div>

    {{-- STEP 1: choose the cases (GET: filters live in the URL) --}}
    <form method="GET" action="{{ route('banks.scope.edit', $bank->id) }}" class="card filter-bar">
        <span class="badge badge-outline-info self-center">1</span>

        <div class="w-36">
            <label for="bucket" class="form-label">الشريحة</label>
            <select id="bucket" name="bucket" class="form-input" onchange="this.form.submit()">
                <option value="">الكل</option>
                @foreach($buckets as $bucket) <option value="{{ $bucket }}" @selected($filters['bucket'] === (string) $bucket)>{{ $bucket }}</option> @endforeach
            </select>
        </div>

        <div class="w-44">
            <label for="loan_type" class="form-label">نوع القرض</label>
            <select id="loan_type" name="loan_type" class="form-input" onchange="this.form.submit()">
                <option value="">الكل</option>
                @foreach($loanTypes as $type) <option value="{{ $type }}" @selected($filters['loan_type'] === $type)>{{ $type }}</option> @endforeach
            </select>
        </div>

        <div class="w-36">
            <label for="status" class="form-label">الحالة</label>
            <select id="status" name="status" class="form-input" onchange="this.form.submit()">
                <option value="">الكل</option>
                @foreach($statuses as $status) <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $status }}</option> @endforeach
            </select>
        </div>

        <div class="w-48">
            <label for="employee" class="form-label">الموظف</label>
            <select id="employee" name="employee" class="form-input" onchange="this.form.submit()">
                <option value="">الكل</option>
                <option value="0" @selected($filters['employee'] === '0')>بدون موظف</option>
                @foreach($employees as $id => $name) <option value="{{ $id }}" @selected($filters['employee'] === (string) $id)>{{ $name }}</option> @endforeach
            </select>
        </div>

        <a href="{{ route('banks.scope.edit', $bank->id) }}" class="btn btn-secondary" title="إعادة ضبط"><i class="fa-solid fa-rotate-right"></i></a>

        <span class="mr-auto self-center text-sm text-muted">المطابقة: <b class="text-brand">{{ $matched->count() }}</b> من {{ $total }}</span>
    </form>

    {{-- PREVIEW --}}
    <x-panel title="معاينة الحالات التي سيتم تعديلها" icon="fa-eye" class="mb-6">
        <div class="table-wrap">
            <table class="data-table whitespace-nowrap">
                <thead><tr><th>الكود</th><th>العميل</th><th>نوع القرض</th><th>الشريحة</th><th>الحالة</th><th>الموظف</th></tr></thead>
                <tbody>
                    @forelse($matched as $case)
                        <tr>
                            <td><span class="badge badge-info font-mono">{{ $case->code }}</span></td>
                            <td class="font-semibold text-fg">{{ $case->name }}</td>
                            <td>{{ $case->loan_type }}</td>
                            <td><span class="badge badge-neutral">{{ $case->bucket }}</span></td>
                            <td><span class="badge badge-success">{{ $case->status }}</span></td>
                            <td>
                                @if($case->employee_name) {{ $case->employee_name }} @else <span class="text-warning">بدون موظف</span> @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-empty-state text="لا توجد حالات مطابقة للفلاتر" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-panel>

    {{-- STEP 2: what to change (PUT) --}}
    <form method="POST" action="{{ route('banks.scope.update', $bank->id) }}" class="max-w-3xl space-y-4">
        @csrf
        @method('PUT')

        {{-- the same filters travel with the update, so the server re-applies them --}}
        @foreach($filters as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach

        <x-section-card title="2 · ماذا تريد أن تغيّر؟" icon="fa-pen-to-square" color="brand">
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <x-select-field name="field" label="الحقل" :options="$fields" placeholder="اختر الحقل..." />

                {{-- only the block of the selected field is visible --}}
                <div data-value-for="assigned_user_id" class="hidden">
                    <x-select-field name="value_employee" label="الموظف الجديد" :options="[0 => 'بدون موظف'] + $employees" placeholder="اختر..." />
                </div>
                <div data-value-for="status" class="hidden">
                    <x-select-field name="value_status" label="الحالة الجديدة" :options="array_combine($statuses, $statuses)" placeholder="اختر..." />
                </div>
                <div data-value-for="bucket" class="hidden">
                    <x-form-field name="value_bucket" label="الشريحة الجديدة" type="number" min="0" max="9" />
                </div>
                <div data-value-for="loan_type" class="hidden">
                    <x-select-field name="value_loan_type" label="نوع القرض الجديد" :options="array_combine($loanTypes, $loanTypes)" placeholder="اختر..." />
                </div>
                <div data-value-for="next_due_date" class="hidden">
                    <x-form-field name="value_due_date" label="تاريخ الاستحقاق الجديد" type="date" />
                </div>
            </div>

            <p id="summary" class="mt-4 text-sm text-muted"></p>
        </x-section-card>

        <label class="check-row">
            <input type="checkbox" name="confirm" value="1" class="accent-brand" @checked(old('confirm'))>
            <span>أفهم أن التعديل سيُطبَّق على <b class="text-fg">{{ $matched->count() }}</b> حالة ولا يمكن التراجع عنه من هذه الصفحة.</span>
        </label>
        @error('confirm') <p class="form-error">{{ $message }}</p> @enderror

        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary" @disabled($matched->isEmpty())><i class="fa-solid fa-play text-xs"></i> تنفيذ التعديل</button>
            <a href="{{ route('banks.panel', $bank->id) }}" class="btn btn-secondary">إلغاء</a>
        </div>
    </form>

@endsection

@push('scripts')
    <script>
        const fieldSelect = document.getElementById('field');
        const summary     = document.getElementById('summary');
        const fieldLabels = @json($fields);
        const matchedCount = {{ $matched->count() }};

        // show only the input of the selected field + a sentence that explains what will happen
        function refresh() {
            const field = fieldSelect.value;

            document.querySelectorAll('[data-value-for]').forEach(block => {
                block.classList.toggle('hidden', block.dataset.valueFor !== field);
            });

            summary.textContent = field
                ? `سيتم تغيير «${fieldLabels[field]}» في ${matchedCount} حالة.`
                : 'اختر الحقل الذي تريد تغييره.';
        }

        fieldSelect.addEventListener('change', refresh);
        refresh();
    </script>
@endpush