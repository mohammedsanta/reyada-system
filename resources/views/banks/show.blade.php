{{-- resources/views/banks/show.blade.php : clients of one bank + edit modal --}}
@extends('layouts.app')

@section('title', $bank->name)

@php
    // 1500 => "1,500" | 948.55 => "948.55"
    $money = fn ($value) => number_format((float) $value, fmod((float) $value, 1) == 0 ? 0 : 2);
@endphp

@section('content')

    {{-- HEADER --}}
    <header class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <span class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-xl bg-white text-black">
                @if($bank->logo ?? null)
                    <img src="{{ $bank->logo }}" alt="{{ $bank->name }}" class="h-full w-full object-contain p-1">
                @else
                    <i class="fa-solid fa-building-columns text-xl"></i>
                @endif
            </span>

            <div>
                <h1 class="text-2xl font-bold text-fg">{{ $bank->name }}</h1>
                <p class="mt-1 flex items-center gap-2 text-xs text-muted">
                    قطاع العملاء
                    <span class="badge badge-outline-info">{{ $caseCount }} حالات</span>
                </p>
            </div>
        </div>

        <a href="{{ route('banks.index') }}" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-arrow-right text-xs"></i> رجوع
        </a>
        <a href="{{ route('banks.ptp.index', $bank->id) }}" class="btn btn-outline-info btn-sm">
            <i class="fa-solid fa-handshake"></i> مركز الوعود (PTP)
        </a>
    </header>

    <x-flash />


    {{-- STATISTICS --}}
    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="حالات تمت معالجتها" :value="$stats['processed']" unit="حالة" />
        <x-stat-card label="إجمالي المتأخرات" :value="$money($stats['arrears'])" unit="EGP" />
        <x-stat-card label="المحصل" :value="$money($stats['collected'])" unit="EGP" />
        <x-stat-card label="المتبقي" :value="$money($stats['remaining'])" unit="EGP" />
    </section>


    {{-- FILTERS (GET form: the URL keeps the filters) --}}
    <form id="filters" method="GET" action="{{ route('banks.show', $bank->id) }}" class="card filter-bar">

        <div class="min-w-[240px] flex-1">
            <label for="search" class="sr-only">بحث</label>
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-dim"></i>
                <input id="search" name="search" type="search" value="{{ $filters['search'] }}"
                       placeholder="ابحث بالاسم، البريد، الكود أو الهاتف..." class="form-input pl-9">
            </div>
        </div>

        <div class="w-40">
            <label for="loan_type" class="form-label">نوع القرض</label>
            <select id="loan_type" name="loan_type" class="form-input" onchange="this.form.submit()">
                <option value="">الكل</option>
                @foreach($loanTypes as $type)
                    <option value="{{ $type }}" @selected($filters['loan_type'] === $type)>{{ $type }}</option>
                @endforeach
            </select>
        </div>

        <div class="w-32">
            <label for="bucket" class="form-label">الشريحة</label>
            <select id="bucket" name="bucket" class="form-input" onchange="this.form.submit()">
                <option value="">الكل</option>
                @foreach($buckets as $bucket)
                    <option value="{{ $bucket }}" @selected($filters['bucket'] === (string) $bucket)>{{ $bucket }}</option>
                @endforeach
            </select>
        </div>

        <div class="w-40">
            <label for="employee" class="form-label">الموظف</label>
            <select id="employee" name="employee" class="form-input" onchange="this.form.submit()">
                <option value="">الكل</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee }}" @selected($filters['employee'] === $employee)>{{ $employee }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter text-xs"></i> فلترة</button>
        <a href="{{ route('banks.show', $bank->id) }}" class="btn btn-secondary" title="إعادة ضبط">
            <i class="fa-solid fa-rotate-right"></i>
        </a>
    </form>


    {{-- BULK TOOLBAR (TODO: connect each button to a real route) --}}
    <div class="mb-3 flex items-center gap-2">
        <button type="button" class="icon-btn icon-btn-danger" data-needs-selection title="حذف المحدد"><i class="fa-solid fa-trash"></i></button>
        <button type="button" class="icon-btn icon-btn-info" title="مزامنة"><i class="fa-solid fa-arrows-rotate"></i></button>
        <button type="button" class="icon-btn icon-btn-warning" title="سجل العمليات"><i class="fa-solid fa-clock-rotate-left"></i></button>
        <button type="button" class="icon-btn icon-btn-cyan" data-needs-selection title="تعيين لموظف"><i class="fa-solid fa-user-plus"></i></button>
        <button type="button" class="icon-btn icon-btn-success" title="تصدير إكسل"><i class="fa-solid fa-file-excel"></i></button>

        <span class="mr-2 text-xs text-muted">المحدد: <b id="selected-count" class="text-fg">0</b></span>
    </div>


    {{-- TABLE --}}
    <div class="table-wrap">
        <table class="data-table whitespace-nowrap">
            <thead>
                <tr>
                    <th class="w-10"><input id="check-all" type="checkbox" class="accent-brand" aria-label="تحديد الكل"></th>
                    <th>كود العميل</th>
                    <th>اسم العميل</th>
                    <th>NATIONAL ID</th>
                    <th>المحافظة / ADDRESS</th>
                    <th>PHONE</th>
                    <th>الشريحة (BUCKET)</th>
                    <th>نوع القرض</th>
                    <th>إجمالي المديونية</th>
                    <th>المبلغ المتأخر</th>
                    <th>غرامة التأخر</th>
                    <th>الموظف</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>

            <tbody>
                @forelse($clients as $client)
                    <tr>
                        <td><input type="checkbox" class="row-check accent-brand" value="{{ $client->id }}" aria-label="تحديد {{ $client->name }}"></td>

                        <td><span class="badge badge-info font-mono">{{ $client->code }}</span></td>

                        <td>
                            <button type="button" data-open-client="{{ $client->id }}"
                                    class="font-semibold text-fg transition hover:text-brand">
                                {{ $client->name }}
                            </button>
                        </td>

                        <td><span dir="ltr">{{ $client->national_id }}</span></td>

                        <td class="whitespace-normal min-w-[260px]">{{ $client->address }} / {{ $client->governorate }}</td>

                        <td class="text-info"><span dir="ltr">{{ $client->phone }} / {{ $client->phone2 }}</span></td>

                        <td><span class="badge badge-neutral">{{ $client->bucket }}</span></td>

                        <td>{{ $client->loan_type }}</td>

                        <td class="font-bold text-fg">EGP {{ $money($client->total_debt) }}</td>
                        <td class="font-semibold text-danger">EGP {{ $money($client->overdue_amount) }}</td>
                        <td class="font-semibold text-warning">EGP {{ $money($client->late_fee) }}</td>

                        <td class="font-semibold text-fg">{{ $client->employee }}</td>

                        {{-- Row menu --}}
                        <td>
                            <details class="relative" data-menu>
                                <summary class="icon-btn icon-btn-info cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </summary>

                                <div class="absolute left-0 top-full z-20 mt-1 w-44 rounded-xl border border-line bg-surface p-1 shadow-xl shadow-black/40">
                                    <button type="button" data-open-client="{{ $client->id }}" class="menu-item">
                                        <i class="fa-solid fa-pen-to-square w-4 text-info"></i> عرض / تعديل
                                    </button>

                                    <a href="#" class="menu-item">
                                        <i class="fa-solid fa-user-plus w-4 text-cyan"></i> تعيين لموظف
                                    </a>

                                    <form method="POST"
                                          action="{{ route('banks.clients.destroy', [$bank->id, $client->id]) }}"
                                          onsubmit="return confirm('هل أنت متأكد من حذف العميل؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="menu-item text-danger">
                                            <i class="fa-solid fa-trash w-4"></i> حذف
                                        </button>
                                    </form>
                                </div>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13" class="py-10 text-center text-dim">لا يوجد عملاء مطابقون</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>


    {{-- FOOTER: rows per page + pagination --}}
    <div class="mt-4 flex flex-wrap items-center justify-between gap-4 text-xs text-muted">
        <div class="flex items-center gap-3">
            <span>
                @if($clients->total() > 0)
                    عرض {{ $clients->firstItem() }} - {{ $clients->lastItem() }} من {{ $clients->total() }}
                @else
                    لا توجد نتائج
                @endif
            </span>

            <label class="flex items-center gap-2">
                عدد الصفوف
                {{-- form="filters" attaches this select to the filter form above --}}
                <select name="per_page" form="filters" class="form-input w-20 py-1.5" onchange="this.form.submit()">
                    @foreach($perPageOptions as $option)
                        <option value="{{ $option }}" @selected($filters['per_page'] === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </label>
        </div>

        <div class="flex items-center gap-3">
            @if($clients->previousPageUrl())
                <a href="{{ $clients->previousPageUrl() }}" class="btn btn-secondary btn-sm"><i class="fa-solid fa-chevron-right text-[10px]"></i> السابق</a>
            @else
                <span class="btn btn-secondary btn-sm" aria-disabled="true" style="opacity:.4;pointer-events:none"><i class="fa-solid fa-chevron-right text-[10px]"></i> السابق</span>
            @endif

            <span>صفحة <b class="text-fg">{{ $clients->currentPage() }}</b> / {{ max(1, $clients->lastPage()) }}</span>

            @if($clients->nextPageUrl())
                <a href="{{ $clients->nextPageUrl() }}" class="btn btn-secondary btn-sm">التالي <i class="fa-solid fa-chevron-left text-[10px]"></i></a>
            @else
                <span class="btn btn-secondary btn-sm" aria-disabled="true" style="opacity:.4;pointer-events:none">التالي <i class="fa-solid fa-chevron-left text-[10px]"></i></span>
            @endif
        </div>
    </div>


    {{-- ======================= EDIT MODAL ======================= --}}
    <dialog id="clientModal" class="modal">
        <form id="clientForm" method="POST" action="#">
            @csrf
            @method('PUT')
            <input type="hidden" name="_client_id" value="">

            {{-- Modal header --}}
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-line px-6 py-4">
                <div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-pen text-xs text-dim"></i>
                        <input name="name" data-field="name" class="inline-input" aria-label="اسم العميل">
                    </div>
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror

                    <p class="mt-1 text-xs text-muted">
                        <span dir="ltr" data-bind="national_id"></span>
                        <span class="mx-1">•</span>
                        <span dir="ltr" class="text-info" data-bind="code"></span>
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <span id="modal-status" class="badge badge-success"></span>

                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-cloud-arrow-up text-xs"></i> حفظ التعديلات
                    </button>

                    <button type="button" class="btn btn-secondary btn-sm" data-close-modal aria-label="إغلاق">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>

            {{-- Modal body: 2 columns (first column = right side in RTL) --}}
            <div class="grid gap-4 p-6 lg:grid-cols-2">

                <div class="space-y-4">
                    <x-section-card title="البيانات الشخصية والاتصال" icon="fa-id-card" color="info">
                        <div class="grid grid-cols-2 gap-3">
                            <x-client-field name="code" label="كود العميل" :readonly="true" :ltr="true" />
                            <x-client-field name="national_id" label="NATIONAL ID" :ltr="true" />
                            <x-client-field name="loan_type" label="نوع القرض" :options="$loanTypes" />
                            <x-client-field name="status" label="حالة العميل" :options="$statuses" />
                            <x-client-field name="phone" label="الهاتف الأساسي" :ltr="true" />
                            <x-client-field name="phone2" label="الهاتف البديل" :ltr="true" />
                            <x-client-field name="email" label="البريد الإلكتروني" type="email" :ltr="true" class="col-span-2" />
                            <x-client-field name="governorate" label="المحافظة" />
                            <x-client-field name="address" label="العنوان بالتفصيل" />
                        </div>
                    </x-section-card>

                    <x-section-card title="التواريخ ومدفوعات العميل" icon="fa-calendar-days" color="info">
                        <div class="grid grid-cols-2 gap-3">
                            <x-client-field name="loan_start_date" label="تاريخ منح القرض" type="date" />
                            <x-client-field name="loan_end_date" label="تاريخ انتهاء القرض" type="date" />
                            <x-client-field name="last_payment_date" label="تاريخ آخر دفعة" type="date" />
                            <x-client-field name="last_payment_amount" label="قيمة آخر دفعة" type="number" step="0.01" />
                        </div>
                    </x-section-card>
                </div>

                <div class="space-y-4">
                    <x-section-card title="تفاصيل المديونية" icon="fa-hand-holding-dollar" color="brand">
                        <div class="grid grid-cols-3 gap-3">
                            <x-client-field name="total_debt" label="إجمالي المديونية" type="number" step="0.01" />
                            <x-client-field name="overdue_amount" label="المبلغ المتأخر" type="number" step="0.01" />
                            <x-client-field name="installment_value" label="قيمة القسط" type="number" step="0.01" />
                            <x-client-field name="bucket" label="الشريحة (BUCKET)" type="number" step="1" />
                            <x-client-field name="dpd" label="DPD" type="number" step="1" />
                            <x-client-field name="min_installment_diff" label="أقل قسط (DIFF)" type="number" step="0.01" />
                            <x-client-field name="next_due_date" label="تاريخ الاستحقاق القادم" type="date" class="col-span-2" />
                            <x-client-field name="late_fee" label="غرامة التأخر" type="number" step="0.01" />
                        </div>
                    </x-section-card>

                    <x-section-card title="بيانات العمل" icon="fa-briefcase" color="warning">
                        <div class="grid grid-cols-2 gap-3">
                            <x-client-field name="employer" label="جهة العمل" />
                            <x-client-field name="job_title" label="المسمى الوظيفي" />
                            <x-client-field name="work_phone" label="تليفون العمل" :ltr="true" />
                            <x-client-field name="work_address" label="عنوان العمل" />
                        </div>
                    </x-section-card>
                </div>

            </div>
        </form>
    </dialog>

@endsection


@push('scripts')
    <script>
        const clients   = @json($clientsJson);                       // all clients of this bank, keyed by id
        const updateUrl = @json(route('banks.clients.update', ['bank' => $bank->id, 'client' => '__ID__']));

        const dialog = document.getElementById('clientModal');
        const form   = document.getElementById('clientForm');

        // Fill the modal with one client's data and open it
        function openClient(id, overrides = {}) {
            const client = { ...clients[id], ...overrides };

            form.action = updateUrl.replace('__ID__', id);
            form.querySelector('[name="_client_id"]').value = id;

            form.querySelectorAll('[data-field]').forEach(el => el.value = client[el.dataset.field] ?? '');
            form.querySelectorAll('[data-bind]').forEach(el => el.textContent = client[el.dataset.bind] ?? '');

            const badge = document.getElementById('modal-status');
            badge.textContent = client.status ?? '';
            badge.className   = 'badge ' + (client.status === 'ACTIVE' ? 'badge-success' : 'badge-danger');

            dialog.showModal();
        }

        document.addEventListener('click', (event) => {
            // open modal (name link + row menu)
            const opener = event.target.closest('[data-open-client]');
            if (opener) {
                opener.closest('details')?.removeAttribute('open');
                openClient(opener.dataset.openClient);
                return;
            }

            // close modal (X button or click on the dark backdrop)
            if (event.target.closest('[data-close-modal]') || event.target === dialog) {
                dialog.close();
            }

            // close any open row menu when clicking elsewhere
            document.querySelectorAll('details[data-menu][open]').forEach(menu => {
                if (!menu.contains(event.target)) menu.removeAttribute('open');
            });
        });

        // Select all / selected count / enable bulk buttons
        const checkAll = document.getElementById('check-all');
        const counter  = document.getElementById('selected-count');
        const rowChecks = () => [...document.querySelectorAll('.row-check')];

        function refreshSelection() {
            const selected = rowChecks().filter(c => c.checked).length;
            counter.textContent = selected;
            checkAll.checked = selected > 0 && selected === rowChecks().length;
            document.querySelectorAll('[data-needs-selection]').forEach(btn => btn.disabled = selected === 0);
        }

        checkAll.addEventListener('change', () => {
            rowChecks().forEach(c => c.checked = checkAll.checked);
            refreshSelection();
        });
        rowChecks().forEach(c => c.addEventListener('change', refreshSelection));
        refreshSelection();

        // Validation failed after saving: reopen the modal with what the user typed
        @if($errors->any() && old('_client_id'))
            openClient(@json(old('_client_id')), @json(old()));
        @endif
    </script>
@endpush