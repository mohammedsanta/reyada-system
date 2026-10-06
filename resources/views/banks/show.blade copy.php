```blade
{{-- resources/views/banks/show.blade.php --}}
@extends('layouts.app')

@section('title', $bank->name)

@php
    /*
    |--------------------------------------------------------------------------
    | Safe helpers
    |--------------------------------------------------------------------------
    */

    $money = fn ($value) => number_format(
        (float) $value,
        fmod((float) $value, 1) == 0 ? 0 : 2
    );

    $clientsJsonSafe = $clientsJson ?? [];

    /*
    |--------------------------------------------------------------------------
    | Frontend-only employee list
    |--------------------------------------------------------------------------
    | Used only by the frontend demo "change employee" modal.
    | It does not save anything to the database.
    */

    $frontendEmployees = \App\Support\StaticData::employees()
        ->mapWithKeys(function ($employee) {
            return [
                $employee->id => $employee->name . ' (' . $employee->role . ')'
            ];
        })
        ->all();
@endphp

@section('content')

    {{-- ============================================================
         PAGE HEADER
    ============================================================= --}}

    <header class="mb-6 flex flex-wrap items-center justify-between gap-4">

        <div class="flex items-center gap-4">

            <span class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-xl bg-white text-black">

                @if($bank->logo ?? null)

                    <img
                        src="{{ $bank->logo }}"
                        alt="{{ $bank->name }}"
                        class="h-full w-full object-contain p-1"
                    >

                @else

                    <i class="fa-solid fa-building-columns text-xl"></i>

                @endif

            </span>

            <div>

                <h1 class="text-2xl font-bold text-fg">
                    {{ $bank->name }}
                </h1>

                <p class="mt-1 flex items-center gap-2 text-xs text-muted">

                    قطاع العملاء

                    <span class="badge badge-outline-info">
                        {{ $caseCount }} حالات
                    </span>

                </p>

            </div>

        </div>

        <a
            href="{{ route('banks.index') }}"
            class="btn btn-secondary btn-sm"
        >
            <i class="fa-solid fa-arrow-right text-xs"></i>
            رجوع
        </a>

    </header>


    {{-- ============================================================
         FLASH
    ============================================================= --}}

    <x-flash />


    {{-- ============================================================
         STATISTICS
    ============================================================= --}}

    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <x-stat-card
            label="حالات تمت معالجتها"
            :value="$stats['processed']"
            unit="حالة"
        />

        <x-stat-card
            label="إجمالي المتأخرات"
            :value="$money($stats['arrears'])"
            unit="EGP"
        />

        <x-stat-card
            label="المحصل"
            :value="$money($stats['collected'])"
            unit="EGP"
        />

        <x-stat-card
            label="المتبقي"
            :value="$money($stats['remaining'])"
            unit="EGP"
        />

    </section>


    {{-- ============================================================
         FILTERS
    ============================================================= --}}

    <form
        id="filters"
        method="GET"
        action="{{ route('banks.show', $bank->id) }}"
        class="card filter-bar"
    >

        <div class="flex min-w-[220px] flex-1 items-center gap-2">

            <i class="fa-solid fa-magnifying-glass text-muted"></i>

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="بحث بالاسم أو الكود أو الرقم القومي..."
                class="input w-full"
            >

        </div>

        <input
            type="text"
            name="loan_type"
            value="{{ request('loan_type') }}"
            placeholder="نوع القرض"
            class="input w-full sm:w-40"
        >

        <input
            type="text"
            name="bucket"
            value="{{ request('bucket') }}"
            placeholder="الشريحة"
            class="input w-full sm:w-32"
        >

        <input
            type="text"
            name="employee"
            value="{{ request('employee') }}"
            placeholder="الموظف"
            class="input w-full sm:w-40"
        >

        <button
            type="submit"
            class="btn btn-primary btn-sm"
        >
            <i class="fa-solid fa-filter text-xs"></i>
            تطبيق
        </button>

        @if(request()->hasAny(['search', 'loan_type', 'bucket', 'employee']))

            <a
                href="{{ route('banks.show', $bank->id) }}"
                class="btn btn-secondary btn-sm"
            >
                <i class="fa-solid fa-xmark text-xs"></i>
                مسح
            </a>

        @endif

    </form>


    {{-- ============================================================
         ACTIVE FILTERS
    ============================================================= --}}

    @if(request()->hasAny(['search', 'loan_type', 'bucket', 'employee']))

        <div class="mb-4 flex flex-wrap items-center gap-2">

            <span class="text-xs text-muted">
                الفلاتر النشطة:
            </span>

            @foreach([
                'search' => 'بحث',
                'loan_type' => 'نوع القرض',
                'bucket' => 'الشريحة',
                'employee' => 'الموظف'
            ] as $filterKey => $filterLabel)

                @if(request($filterKey))

                    <span class="badge badge-outline-info">
                        {{ $filterLabel }}:
                        {{ request($filterKey) }}
                    </span>

                @endif

            @endforeach

        </div>

    @endif


    {{-- ============================================================
         ACTION TOOLBAR
    ============================================================= --}}

    <div class="mb-3 flex flex-wrap items-center gap-2">

        <button
            type="button"
            class="icon-btn icon-btn-danger"
            data-needs-selection
            title="حذف المحدد"
            aria-label="حذف المحدد"
        >
            <i class="fa-solid fa-trash"></i>
        </button>

        <button
            type="button"
            class="icon-btn icon-btn-info"
            id="syncClientsButton"
            title="مزامنة"
            aria-label="مزامنة"
        >
            <i class="fa-solid fa-arrows-rotate"></i>
        </button>

        <button
            type="button"
            class="icon-btn icon-btn-warning"
            id="activityLogButton"
            title="سجل العمليات"
            aria-label="سجل العمليات"
        >
            <i class="fa-solid fa-clock-rotate-left"></i>
        </button>

        <button
            type="button"
            class="icon-btn icon-btn-cyan"
            data-needs-selection
            data-open-modal="assignModal"
            title="تعيين لموظف"
            aria-label="تعيين لموظف"
        >
            <i class="fa-solid fa-user-plus"></i>
        </button>

        <button
            type="button"
            class="icon-btn icon-btn-success"
            id="exportSelectedButton"
            data-needs-selection
            title="تصدير المحدد"
            aria-label="تصدير المحدد"
        >
            <i class="fa-solid fa-file-excel"></i>
        </button>

        <span class="mr-2 text-xs text-muted">
            المحدد:
            <b id="selected-count" class="text-fg">0</b>
        </span>

    </div>


    {{-- ============================================================
         CLIENT TABLE
    ============================================================= --}}

    <div class="table-wrap">

        <table class="data-table whitespace-nowrap">

            <thead>

                <tr>

                    <th class="w-10">

                        <input
                            id="check-all"
                            type="checkbox"
                            class="accent-brand"
                            aria-label="تحديد الكل"
                        >

                    </th>

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

                        {{-- Selection --}}
                        <td>

                            <input
                                type="checkbox"
                                class="row-check accent-brand"
                                value="{{ $client->id }}"
                                aria-label="تحديد {{ $client->name }}"
                            >

                        </td>


                        {{-- Code --}}
                        <td>

                            <span class="badge badge-info font-mono">
                                {{ $client->code }}
                            </span>

                        </td>


                        {{-- Name --}}
                        <td>

                            <button
                                type="button"
                                data-open-client="{{ $client->id }}"
                                class="font-semibold text-fg transition hover:text-brand"
                            >
                                {{ $client->name }}
                            </button>

                        </td>


                        {{-- National ID --}}
                        <td>

                            <span dir="ltr">
                                {{ $client->national_id ?: '-' }}
                            </span>

                        </td>


                        {{-- Address --}}
                        <td class="min-w-[260px] whitespace-normal">

                            {{ $client->address ?: '-' }}

                            @if($client->governorate)
                                <span class="text-dim">
                                    /
                                    {{ $client->governorate }}
                                </span>
                            @endif

                        </td>


                        {{-- Phones --}}
                        <td class="text-info">

                            <span dir="ltr">

                                {{ $client->phone ?: '-' }}

                                @if($client->phone2)
                                    /
                                    {{ $client->phone2 }}
                                @endif

                            </span>

                        </td>


                        {{-- Bucket --}}
                        <td>

                            <span class="badge badge-neutral">
                                {{ $client->bucket ?: '-' }}
                            </span>

                        </td>


                        {{-- Loan --}}
                        <td>
                            {{ $client->loan_type ?: '-' }}
                        </td>


                        {{-- Total Debt --}}
                        <td class="font-bold text-fg">

                            EGP
                            {{ $money($client->total_debt) }}

                        </td>


                        {{-- Overdue --}}
                        <td class="font-semibold text-danger">

                            EGP
                            {{ $money($client->overdue_amount) }}

                        </td>


                        {{-- Late Fee --}}
                        <td class="font-semibold text-warning">

                            EGP
                            {{ $money($client->late_fee) }}

                        </td>


                        {{-- Employee --}}
                        <td class="font-semibold text-fg">

                            {{ $client->employee ?: '-' }}

                        </td>


                        {{-- Actions --}}
                        <td>

                            <details class="relative" data-menu>

                                <summary
                                    class="icon-btn icon-btn-info cursor-pointer list-none [&::-webkit-details-marker]:hidden"
                                    title="إجراءات العميل"
                                    aria-label="إجراءات العميل"
                                >
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </summary>


                                {{-- ====================================================
                                     PROFESSIONAL ACTION MENU
                                ===================================================== --}}

                                <div class="absolute left-0 top-full z-30 mt-1 w-52 rounded-xl border border-line bg-surface p-1.5 shadow-xl shadow-black/40">

                                    {{-- Client --}}
                                    <div class="px-2 py-1.5 text-[10px] font-semibold text-dim">
                                        العميل
                                    </div>

                                    <button
                                        type="button"
                                        data-open-client="{{ $client->id }}"
                                        class="menu-item"
                                    >
                                        <i class="fa-solid fa-pen-to-square w-4 text-info"></i>
                                        عرض / تعديل
                                    </button>

                                    <button
                                        type="button"
                                        data-open-client-history="{{ $client->id }}"
                                        class="menu-item"
                                    >
                                        <i class="fa-solid fa-clock-rotate-left w-4 text-info"></i>
                                        سجل العميل
                                    </button>

                                    <button
                                        type="button"
                                        data-open-payment-history="{{ $client->id }}"
                                        class="menu-item"
                                    >
                                        <i class="fa-solid fa-money-bill-wave w-4 text-brand"></i>
                                        سجل المدفوعات
                                    </button>


                                    {{-- Collection --}}
                                    <div class="my-1 border-t border-line"></div>

                                    <div class="px-2 py-1.5 text-[10px] font-semibold text-dim">
                                        التحصيل
                                    </div>

                                    <button
                                        type="button"
                                        data-open-promise="{{ $client->id }}"
                                        class="menu-item"
                                    >
                                        <i class="fa-solid fa-handshake w-4 text-warning"></i>
                                        وعد بالسداد
                                    </button>

                                    <button
                                        type="button"
                                        data-open-payment="{{ $client->id }}"
                                        class="menu-item"
                                    >
                                        <i class="fa-solid fa-credit-card w-4 text-brand"></i>
                                        تسجيل دفعة
                                    </button>


                                    {{-- Employee --}}
                                    <div class="my-1 border-t border-line"></div>

                                    <div class="px-2 py-1.5 text-[10px] font-semibold text-dim">
                                        الموظف
                                    </div>

                                    <button
                                        type="button"
                                        data-open-current-employee="{{ $client->id }}"
                                        class="menu-item"
                                    >
                                        <i class="fa-solid fa-user w-4 text-info"></i>
                                        الموظف الحالي
                                    </button>

                                    <button
                                        type="button"
                                        data-assign-one="{{ $client->id }}"
                                        class="menu-item"
                                    >
                                        <i class="fa-solid fa-user-plus w-4 text-cyan"></i>
                                        تعيين لموظف
                                    </button>

                                    <button
                                        type="button"
                                        data-change-employee="{{ $client->id }}"
                                        class="menu-item"
                                    >
                                        <i class="fa-solid fa-arrows-rotate w-4 text-warning"></i>
                                        تغيير الموظف
                                    </button>


                                    {{-- More --}}
                                    <div class="my-1 border-t border-line"></div>

                                    <button
                                        type="button"
                                        data-open-client-tools="{{ $client->id }}"
                                        class="menu-item"
                                    >
                                        <i class="fa-solid fa-ellipsis w-4 text-muted"></i>
                                        المزيد
                                        <i class="fa-solid fa-chevron-left mr-auto text-[9px] text-dim"></i>
                                    </button>


                                    {{-- Delete --}}
                                    <div class="my-1 border-t border-line"></div>

                                    <form
                                        method="POST"
                                        action="{{ route('banks.clients.destroy', [$bank->id, $client->id]) }}"
                                        onsubmit="return confirm('هل أنت متأكد من حذف العميل؟')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="menu-item text-danger"
                                        >
                                            <i class="fa-solid fa-trash w-4"></i>
                                            حذف العميل
                                        </button>

                                    </form>

                                </div>

                            </details>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="13" class="py-10 text-center text-dim">

                            <x-empty-state
                                text="لا يوجد عملاء مطابقون"
                            />

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>


    {{-- ============================================================
         PAGINATION
    ============================================================= --}}

    @if(method_exists($clients, 'total'))

        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">

            <div class="text-xs text-muted">

                عرض

                <span class="font-semibold text-fg">
                    {{ $clients->firstItem() ?? 0 }}
                </span>

                إلى

                <span class="font-semibold text-fg">
                    {{ $clients->lastItem() ?? 0 }}
                </span>

                من

                <span class="font-semibold text-fg">
                    {{ $clients->total() }}
                </span>

                عميل

            </div>


            <div>
                {{ $clients->links() }}
            </div>

        </div>

    @endif


    {{-- ============================================================
         CLIENT EDIT MODAL
    ============================================================= --}}

    <x-modal
        id="clientModal"
        title="بيانات العميل"
        width="52rem"
    >

        <form
            id="clientForm"
            method="POST"
            action="#"
            class="space-y-5"
        >

            @csrf
            @method('PUT')

            <input
                type="hidden"
                id="client-modal-id"
                name="_client_id"
                value=""
            >


            {{-- Identity --}}
            <div>

                <div class="mb-3 flex items-center gap-2">

                    <i class="fa-solid fa-user text-info"></i>

                    <h3 class="font-semibold text-fg">
                        البيانات الأساسية
                    </h3>

                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                    <x-form-field
                        name="code"
                        label="كود العميل"
                        id="modal-code"
                    />

                    <x-form-field
                        name="national_id"
                        label="National ID"
                        id="modal-national-id"
                    />

                    <x-form-field
                        name="loan_type"
                        label="نوع القرض"
                        id="modal-loan-type"
                    />

                    <x-form-field
                        name="status"
                        label="الحالة"
                        id="modal-status"
                    />

                    <x-form-field
                        name="phone"
                        label="الهاتف"
                        id="modal-phone"
                    />

                    <x-form-field
                        name="phone2"
                        label="الهاتف الثاني"
                        id="modal-phone2"
                    />

                    <x-form-field
                        name="email"
                        label="البريد الإلكتروني"
                        id="modal-email"
                    />

                    <x-form-field
                        name="governorate"
                        label="المحافظة"
                        id="modal-governorate"
                    />

                    <div class="md:col-span-2">

                        <x-form-field
                            name="address"
                            label="العنوان"
                            id="modal-address"
                        />

                    </div>

                </div>

            </div>


            {{-- Loan --}}
            <div>

                <div class="mb-3 flex items-center gap-2">

                    <i class="fa-solid fa-money-bill-transfer text-brand"></i>

                    <h3 class="font-semibold text-fg">
                        بيانات المديونية
                    </h3>

                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                    <x-form-field
                        name="loan_start_date"
                        label="تاريخ بداية القرض"
                        id="modal-loan-start-date"
                    />

                    <x-form-field
                        name="loan_end_date"
                        label="تاريخ نهاية القرض"
                        id="modal-loan-end-date"
                    />

                    <x-form-field
                        name="last_payment_date"
                        label="آخر سداد"
                        id="modal-last-payment-date"
                    />

                    <x-form-field
                        name="total_debt"
                        label="إجمالي المديونية"
                        id="modal-total-debt"
                    />

                    <x-form-field
                        name="overdue_amount"
                        label="المبلغ المتأخر"
                        id="modal-overdue-amount"
                    />

                    <x-form-field
                        name="installment_value"
                        label="قيمة القسط"
                        id="modal-installment-value"
                    />

                    <x-form-field
                        name="bucket"
                        label="الشريحة"
                        id="modal-bucket"
                    />

                    <x-form-field
                        name="dpd"
                        label="DPD"
                        id="modal-dpd"
                    />

                    <x-form-field
                        name="min_installment_diff"
                        label="فرق الحد الأدنى للقسط"
                        id="modal-min-installment-diff"
                    />

                    <x-form-field
                        name="next_due_date"
                        label="موعد الاستحقاق القادم"
                        id="modal-next-due-date"
                    />

                    <x-form-field
                        name="late_fee"
                        label="غرامة التأخر"
                        id="modal-late-fee"
                    />

                </div>

            </div>


            {{-- Work --}}
            <div>

                <div class="mb-3 flex items-center gap-2">

                    <i class="fa-solid fa-briefcase text-warning"></i>

                    <h3 class="font-semibold text-fg">
                        بيانات العمل
                    </h3>

                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                    <x-form-field
                        name="employer"
                        label="جهة العمل"
                        id="modal-employer"
                    />

                    <x-form-field
                        name="job_title"
                        label="الوظيفة"
                        id="modal-job-title"
                    />

                    <x-form-field
                        name="work_phone"
                        label="هاتف العمل"
                        id="modal-work-phone"
                    />

                    <x-form-field
                        name="work_address"
                        label="عنوان العمل"
                        id="modal-work-address"
                    />

                </div>

            </div>


            <div class="flex justify-end gap-2 border-t border-line pt-4">

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                    حفظ التعديلات
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


    {{-- ============================================================
         CLIENT TOOLS MODAL
    ============================================================= --}}

    <x-modal
        id="clientToolsModal"
        title="أدوات العميل"
        width="28rem"
    >

        <div class="mb-4 rounded-xl border border-info/20 bg-info/[0.05] p-3">

            <div class="flex items-center gap-3">

                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-info/10 text-info">

                    <i class="fa-solid fa-toolbox"></i>

                </span>

                <div>

                    <div
                        id="tools-client-name"
                        class="font-semibold text-fg"
                    >
                        -
                    </div>

                    <div
                        id="tools-client-code"
                        class="mt-0.5 font-mono text-[11px] text-muted"
                    >
                        -
                    </div>

                </div>

            </div>

        </div>


        <div class="grid grid-cols-2 gap-2">

            <button
                type="button"
                data-open-call-history-from-tools
                class="card flex flex-col items-center justify-center gap-2 p-4 text-center transition hover:border-cyan/40"
            >
                <i class="fa-solid fa-phone-volume text-lg text-cyan"></i>
                <span class="text-xs text-fg">سجل الاتصالات</span>
            </button>

            <button
                type="button"
                data-copy-client-code-from-tools
                class="card flex flex-col items-center justify-center gap-2 p-4 text-center transition hover:border-info/40"
            >
                <i class="fa-solid fa-copy text-lg text-info"></i>
                <span class="text-xs text-fg">نسخ الكود</span>
            </button>

            <button
                type="button"
                data-copy-national-id-from-tools
                class="card flex flex-col items-center justify-center gap-2 p-4 text-center transition hover:border-warning/40"
            >
                <i class="fa-solid fa-id-card text-lg text-warning"></i>
                <span class="text-xs text-fg">نسخ National ID</span>
            </button>

            <button
                type="button"
                data-copy-phone2-from-tools
                class="card flex flex-col items-center justify-center gap-2 p-4 text-center transition hover:border-cyan/40"
            >
                <i class="fa-solid fa-phone-flip text-lg text-cyan"></i>
                <span class="text-xs text-fg">نسخ الهاتف الثاني</span>
            </button>

            <button
                type="button"
                data-copy-address-from-tools
                class="card flex flex-col items-center justify-center gap-2 p-4 text-center transition hover:border-danger/40"
            >
                <i class="fa-solid fa-location-dot text-lg text-danger"></i>
                <span class="text-xs text-fg">نسخ العنوان</span>
            </button>

            <button
                type="button"
                data-unassign-from-tools
                class="card flex flex-col items-center justify-center gap-2 p-4 text-center transition hover:border-danger/40"
            >
                <i class="fa-solid fa-user-minus text-lg text-danger"></i>
                <span class="text-xs text-fg">إلغاء التعيين</span>
            </button>

            <button
                type="button"
                data-export-client-from-tools
                class="card flex flex-col items-center justify-center gap-2 p-4 text-center transition hover:border-info/40"
            >
                <i class="fa-solid fa-file-export text-lg text-info"></i>
                <span class="text-xs text-fg">تصدير البيانات</span>
            </button>

            <button
                type="button"
                data-print-client-from-tools
                class="card flex flex-col items-center justify-center gap-2 p-4 text-center transition hover:border-info/40"
            >
                <i class="fa-solid fa-print text-lg text-info"></i>
                <span class="text-xs text-fg">طباعة الملف</span>
            </button>

        </div>


        <div class="mt-4 flex justify-end">

            <button
                type="button"
                class="btn btn-secondary btn-sm"
                data-close-modal
            >
                إغلاق
            </button>

        </div>

    </x-modal>


    {{-- ============================================================
         CLIENT HISTORY MODAL
    ============================================================= --}}

    <x-modal
        id="clientHistoryModal"
        title="سجل العميل"
        width="42rem"
    >

        <div id="client-history-content">

            <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                <div class="grid grid-cols-2 gap-4 md:grid-cols-4">

                    <div>
                        <span class="text-[10px] text-dim">العميل</span>
                        <div id="history-name" class="mt-1 font-semibold text-fg">-</div>
                    </div>

                    <div>
                        <span class="text-[10px] text-dim">الكود</span>
                        <div id="history-code" class="mt-1 font-mono text-info">-</div>
                    </div>

                    <div>
                        <span class="text-[10px] text-dim">الحالة</span>
                        <div id="history-status" class="mt-1 text-fg">-</div>
                    </div>

                    <div>
                        <span class="text-[10px] text-dim">الموظف</span>
                        <div id="history-employee" class="mt-1 text-fg">-</div>
                    </div>

                </div>

            </div>


            <div class="my-4 border-t border-line"></div>


            <div class="rounded-xl border border-warning/20 bg-warning/[0.04] p-4">

                <div class="flex items-start gap-3">

                    <i class="fa-solid fa-circle-info mt-0.5 text-warning"></i>

                    <div>

                        <div class="text-sm font-semibold text-fg">
                            سجل النشاط
                        </div>

                        <p class="mt-1 text-xs leading-6 text-muted">
                            هذه الواجهة Frontend فقط حالياً.
                            سيتم ربط سجل النشاط الحقيقي بالـ Backend لاحقاً.
                        </p>

                    </div>

                </div>

            </div>


            <div class="mt-4 space-y-2">

                <div class="flex items-center gap-3 rounded-xl border border-line p-3">

                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-info/10 text-info">
                        <i class="fa-solid fa-user"></i>
                    </span>

                    <div class="flex-1">

                        <div class="text-xs font-semibold text-fg">
                            بيانات العميل
                        </div>

                        <div class="text-[11px] text-dim">
                            يتم عرض البيانات الحالية للعميل.
                        </div>

                    </div>

                    <span class="text-[10px] text-dim">
                        حالياً
                    </span>

                </div>

                <div class="flex items-center gap-3 rounded-xl border border-line p-3">

                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand/10 text-brand">
                        <i class="fa-solid fa-money-bill"></i>
                    </span>

                    <div class="flex-1">

                        <div class="text-xs font-semibold text-fg">
                            التحصيل
                        </div>

                        <div class="text-[11px] text-dim">
                            سجل التحصيلات سيتم ربطه لاحقاً.
                        </div>

                    </div>

                    <span class="text-[10px] text-dim">
                        Frontend
                    </span>

                </div>

            </div>

        </div>

    </x-modal>


    {{-- ============================================================
         PAYMENT HISTORY MODAL
    ============================================================= --}}

    <x-modal
        id="paymentHistoryModal"
        title="سجل المدفوعات"
        width="46rem"
    >

        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">

            <div>

                <div
                    id="payment-history-client-name"
                    class="font-semibold text-fg"
                >
                    -
                </div>

                <div
                    id="payment-history-client-code"
                    class="mt-1 font-mono text-[11px] text-muted"
                >
                    -
                </div>

            </div>

            <div class="rounded-xl border border-brand/20 bg-brand/[0.04] px-4 py-2">

                <div class="text-[10px] text-dim">
                    إجمالي المديونية الحالية
                </div>

                <div
                    id="payment-history-debt"
                    class="mt-1 font-bold text-brand"
                >
                    EGP 0
                </div>

            </div>

        </div>


        <div class="table-wrap">

            <table class="data-table whitespace-nowrap">

                <thead>

                    <tr>
                        <th>التاريخ</th>
                        <th>الإيصال</th>
                        <th>المبلغ</th>
                        <th>طريقة الدفع</th>
                        <th>الحالة</th>
                    </tr>

                </thead>

                <tbody>

                    <tr>

                        <td colspan="5" class="py-8 text-center">

                            <div class="flex flex-col items-center gap-2">

                                <i class="fa-solid fa-receipt text-2xl text-dim"></i>

                                <span class="text-sm text-muted">
                                    لا توجد مدفوعات مسجلة في الواجهة الحالية
                                </span>

                                <span class="text-[11px] text-dim">
                                    سيتم ربط البيانات الحقيقية لاحقاً بالـ Backend
                                </span>

                            </div>

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

    </x-modal>


    {{-- ============================================================
         CALL HISTORY MODAL
    ============================================================= --}}

    <x-modal
        id="callHistoryModal"
        title="سجل الاتصالات"
        width="42rem"
    >

        <div class="mb-4 rounded-xl border border-cyan/20 bg-cyan/[0.04] p-4">

            <div class="flex items-center gap-3">

                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-cyan/10 text-cyan">

                    <i class="fa-solid fa-phone-volume"></i>

                </span>

                <div>

                    <div
                        id="call-history-client-name"
                        class="font-semibold text-fg"
                    >
                        -
                    </div>

                    <div
                        id="call-history-client-phone"
                        class="mt-1 text-xs text-muted"
                        dir="ltr"
                    >
                        -
                    </div>

                </div>

            </div>

        </div>


        <div class="space-y-3">

            <div class="flex items-start gap-3 rounded-xl border border-line p-4">

                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/5 text-dim">

                    <i class="fa-solid fa-phone"></i>

                </span>

                <div class="flex-1">

                    <div class="text-sm font-semibold text-fg">
                        لا توجد مكالمات مسجلة
                    </div>

                    <div class="mt-1 text-[11px] leading-5 text-muted">
                        سيتم تسجيل المكالمات وربطها بالـ Backend لاحقاً.
                    </div>

                </div>

                <span class="text-[10px] text-dim">
                    -
                </span>

            </div>

        </div>

    </x-modal>


    {{-- ============================================================
         PROMISE MODAL
    ============================================================= --}}

    <x-modal
        id="promiseModal"
        title="إضافة وعد بالسداد"
        width="34rem"
    >

        <form
            id="promiseForm"
            class="space-y-4"
        >

            <div class="rounded-xl border border-warning/20 bg-warning/[0.04] p-3">

                <div class="flex items-start gap-3">

                    <i class="fa-solid fa-handshake mt-0.5 text-warning"></i>

                    <div>

                        <div
                            id="promise-client-name"
                            class="text-sm font-semibold text-fg"
                        >
                            -
                        </div>

                        <div
                            id="promise-client-code"
                            class="mt-1 font-mono text-[11px] text-muted"
                        >
                            -
                        </div>

                    </div>

                </div>

            </div>


            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                <div>

                    <label class="mb-1 block text-xs text-muted">
                        تاريخ الوعد
                    </label>

                    <input
                        type="date"
                        id="promise-date"
                        class="input w-full"
                    >

                </div>

                <div>

                    <label class="mb-1 block text-xs text-muted">
                        المبلغ المتوقع
                    </label>

                    <input
                        type="number"
                        min="0"
                        step="0.01"
                        id="promise-amount"
                        class="input w-full"
                        placeholder="مثال: 5000"
                    >

                </div>

            </div>


            <div>

                <label class="mb-1 block text-xs text-muted">
                    ملاحظات
                </label>

                <textarea
                    id="promise-note"
                    rows="3"
                    class="input w-full"
                    placeholder="ملاحظات الموظف..."
                ></textarea>

            </div>


            <div class="rounded-xl border border-info/20 bg-info/[0.04] p-3">

                <div class="flex items-start gap-2">

                    <i class="fa-solid fa-circle-info mt-0.5 text-info"></i>

                    <span class="text-[11px] leading-5 text-muted">
                        هذه واجهة Frontend فقط حالياً. لن يتم حفظ الوعد في قاعدة البيانات.
                    </span>

                </div>

            </div>


            <div class="flex justify-end gap-2">

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    <i class="fa-solid fa-check text-xs"></i>
                    تسجيل الوعد
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


    {{-- ============================================================
         PAYMENT MODAL
    ============================================================= --}}

    <x-modal
        id="paymentModal"
        title="تسجيل دفعة"
        width="34rem"
    >

        <form
            id="frontendPaymentForm"
            class="space-y-4"
        >

            <div class="rounded-xl border border-brand/20 bg-brand/[0.04] p-3">

                <div class="flex items-center gap-3">

                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand/10 text-brand">

                        <i class="fa-solid fa-credit-card"></i>

                    </span>

                    <div>

                        <div
                            id="payment-client-name"
                            class="text-sm font-semibold text-fg"
                        >
                            -
                        </div>

                        <div
                            id="payment-client-code"
                            class="mt-1 font-mono text-[11px] text-muted"
                        >
                            -
                        </div>

                    </div>

                </div>

            </div>


            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                <div>

                    <label class="mb-1 block text-xs text-muted">
                        المبلغ
                    </label>

                    <input
                        type="number"
                        min="0"
                        step="0.01"
                        id="frontend-payment-amount"
                        class="input w-full"
                        placeholder="0.00"
                    >

                </div>

                <div>

                    <label class="mb-1 block text-xs text-muted">
                        طريقة الدفع
                    </label>

                    <select
                        id="frontend-payment-method"
                        class="input w-full"
                    >
                        <option value="">اختر الطريقة...</option>
                        <option value="cash">نقدي</option>
                        <option value="bank">تحويل بنكي</option>
                        <option value="wallet">محفظة إلكترونية</option>
                        <option value="other">أخرى</option>
                    </select>

                </div>

            </div>


            <div>

                <label class="mb-1 block text-xs text-muted">
                    رقم الإيصال
                </label>

                <input
                    type="text"
                    id="frontend-payment-receipt"
                    class="input w-full"
                    placeholder="رقم الإيصال"
                >

            </div>


            <div>

                <label class="mb-1 block text-xs text-muted">
                    ملاحظات
                </label>

                <textarea
                    id="frontend-payment-note"
                    rows="3"
                    class="input w-full"
                    placeholder="ملاحظات التحصيل..."
                ></textarea>

            </div>


            <div class="rounded-xl border border-info/20 bg-info/[0.04] p-3">

                <div class="flex items-start gap-2">

                    <i class="fa-solid fa-circle-info mt-0.5 text-info"></i>

                    <span class="text-[11px] leading-5 text-muted">
                        هذه واجهة Frontend فقط حالياً ولن يتم إنشاء سجل حقيقي.
                    </span>

                </div>

            </div>


            <div class="flex justify-end gap-2">

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    <i class="fa-solid fa-check text-xs"></i>
                    تسجيل الدفعة
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


    {{-- ============================================================
         CURRENT EMPLOYEE MODAL
    ============================================================= --}}

    <x-modal
        id="currentEmployeeModal"
        title="الموظف الحالي"
        width="30rem"
    >

        <div class="space-y-4">

            <div class="flex items-center gap-4 rounded-xl border border-line bg-white/[0.02] p-4">

                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-info/10 text-info">

                    <i class="fa-solid fa-user text-lg"></i>

                </span>

                <div class="flex-1">

                    <div
                        id="current-employee-name"
                        class="font-semibold text-fg"
                    >
                        -
                    </div>

                    <div class="mt-1 text-xs text-muted">
                        الموظف المسؤول عن الحالة حالياً
                    </div>

                </div>

            </div>


            <div class="grid grid-cols-2 gap-3">

                <div class="rounded-xl border border-line p-3">

                    <div class="text-[10px] text-dim">
                        العميل
                    </div>

                    <div
                        id="current-employee-client"
                        class="mt-1 text-sm font-semibold text-fg"
                    >
                        -
                    </div>

                </div>

                <div class="rounded-xl border border-line p-3">

                    <div class="text-[10px] text-dim">
                        كود العميل
                    </div>

                    <div
                        id="current-employee-code"
                        class="mt-1 font-mono text-sm text-info"
                    >
                        -
                    </div>

                </div>

            </div>


            <div class="flex justify-end">

                <button
                    type="button"
                    class="btn btn-secondary btn-sm"
                    data-close-modal
                >
                    إغلاق
                </button>

            </div>

        </div>

    </x-modal>


    {{-- ============================================================
         CHANGE EMPLOYEE MODAL
         FRONTEND ONLY
    ============================================================= --}}

    <x-modal
        id="changeEmployeeModal"
        title="تغيير الموظف"
        width="34rem"
    >

        <form
            id="changeEmployeeForm"
            class="space-y-4"
        >

            <div class="rounded-xl border border-warning/20 bg-warning/[0.04] p-3">

                <div class="flex items-center gap-3">

                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-warning/10 text-warning">

                        <i class="fa-solid fa-arrows-rotate"></i>

                    </span>

                    <div>

                        <div
                            id="change-employee-client-name"
                            class="text-sm font-semibold text-fg"
                        >
                            -
                        </div>

                        <div
                            id="change-employee-current"
                            class="mt-1 text-[11px] text-muted"
                        >
                            الموظف الحالي: -
                        </div>

                    </div>

                </div>

            </div>


            <div>

                <label class="mb-1 block text-xs text-muted">
                    الموظف الجديد
                </label>

                <select
                    id="change-employee-select"
                    class="input w-full"
                >

                    <option value="">
                        اختر الموظف...
                    </option>

                    @foreach($frontendEmployees as $employeeId => $employeeLabel)

                        <option value="{{ $employeeId }}">
                            {{ $employeeLabel }}
                        </option>

                    @endforeach

                </select>

            </div>


            <div>

                <label class="mb-1 block text-xs text-muted">
                    سبب التغيير
                </label>

                <textarea
                    id="change-employee-reason"
                    rows="3"
                    class="input w-full"
                    placeholder="سبب إعادة التوزيع..."
                ></textarea>

            </div>


            <div class="rounded-xl border border-info/20 bg-info/[0.04] p-3">

                <div class="flex items-start gap-2">

                    <i class="fa-solid fa-circle-info mt-0.5 text-info"></i>

                    <span class="text-[11px] leading-5 text-muted">
                        هذا النموذج Frontend فقط. لن يتم تغيير الموظف الحقيقي حتى يتم ربط العملية بالـ Backend.
                    </span>

                </div>

            </div>


            <div class="flex justify-end gap-2">

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    <i class="fa-solid fa-arrows-rotate text-xs"></i>
                    معاينة التغيير
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


    {{-- ============================================================
         UNASSIGN MODAL
         FRONTEND ONLY
    ============================================================= --}}

    <x-modal
        id="unassignModal"
        title="إلغاء تعيين العميل"
        width="32rem"
    >

        <form
            id="unassignForm"
            class="space-y-4"
        >

            <div class="rounded-xl border border-danger/20 bg-danger/[0.04] p-4">

                <div class="flex items-start gap-3">

                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-danger/10 text-danger">

                        <i class="fa-solid fa-user-minus"></i>

                    </span>

                    <div>

                        <div class="text-sm font-semibold text-fg">
                            إلغاء تعيين العميل
                        </div>

                        <p class="mt-1 text-xs leading-5 text-muted">
                            سيتم تجهيز عملية إلغاء التعيين فقط في الواجهة حالياً.
                        </p>

                    </div>

                </div>

            </div>


            <div class="rounded-xl border border-line p-4">

                <div class="text-[10px] text-dim">
                    العميل
                </div>

                <div
                    id="unassign-client-name"
                    class="mt-1 font-semibold text-fg"
                >
                    -
                </div>

            </div>


            <div class="rounded-xl border border-info/20 bg-info/[0.04] p-3">

                <div class="flex items-start gap-2">

                    <i class="fa-solid fa-circle-info mt-0.5 text-info"></i>

                    <span class="text-[11px] leading-5 text-muted">
                        لن يتم تعديل قاعدة البيانات في هذه المرحلة.
                    </span>

                </div>

            </div>


            <div class="flex justify-end gap-2">

                <button
                    type="submit"
                    class="btn btn-danger"
                >
                    <i class="fa-solid fa-user-minus text-xs"></i>
                    متابعة
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


    {{-- ============================================================
         SIMPLE INFO MODAL
    ============================================================= --}}

    <x-modal
        id="frontendInfoModal"
        title="معلومة"
        width="28rem"
    >

        <div class="space-y-4">

            <div class="flex items-start gap-3 rounded-xl border border-info/20 bg-info/[0.04] p-4">

                <i class="fa-solid fa-circle-info mt-0.5 text-info"></i>

                <p
                    id="frontend-info-message"
                    class="text-sm leading-6 text-muted"
                >
                    -
                </p>

            </div>

            <div class="flex justify-end">

                <button
                    type="button"
                    class="btn btn-secondary btn-sm"
                    data-close-modal
                >
                    إغلاق
                </button>

            </div>

        </div>

    </x-modal>


    {{-- Existing assignment modal --}}
    @include('banks._assign-modal', ['bank' => $bank])

@endsection


@push('scripts')

<script>
(function () {

    'use strict';


    /* ============================================================
       DATA
    ============================================================= */

    const clients = @json($clientsJsonSafe);

    const updateUrlTemplate = @json(
        route('banks.clients.update', [$bank->id, '__ID__'])
    );


    /*
    |--------------------------------------------------------------------------
    | Normalize client data
    |--------------------------------------------------------------------------
    */

    const clientMap = new Map();

    function normalizeClient(client) {

        if (!client || typeof client !== 'object') {
            return {};
        }

        return client;

    }

    if (Array.isArray(clients)) {

        clients.forEach(function (client) {

            const normalized = normalizeClient(client);

            if (normalized.id !== undefined && normalized.id !== null) {

                clientMap.set(
                    String(normalized.id),
                    normalized
                );

            }

        });

    } else if (clients && typeof clients === 'object') {

        Object.keys(clients).forEach(function (key) {

            const normalized = normalizeClient(clients[key]);

            const id = normalized.id ?? key;

            if (id !== undefined && id !== null) {

                clientMap.set(
                    String(id),
                    normalized
                );

            }

        });

    }


    function getClient(id) {

        return clientMap.get(String(id)) || null;

    }


    /* ============================================================
       DOM HELPERS
    ============================================================= */

    function byId(id) {
        return document.getElementById(id);
    }


    function openModal(id) {

        const modal = byId(id);

        if (!modal) {
            return;
        }

        if (typeof modal.showModal === 'function') {
            modal.showModal();
        }

    }


    function closeModal(id) {

        const modal = byId(id);

        if (!modal) {
            return;
        }

        if (typeof modal.close === 'function') {
            modal.close();
        }

    }


    function setText(id, value) {

        const element = byId(id);

        if (!element) {
            return;
        }

        element.textContent =
            value === null ||
            value === undefined ||
            value === ''
                ? '-'
                : String(value);

    }


    function escapeHtml(value) {

        const div = document.createElement('div');

        div.textContent =
            value === null ||
            value === undefined
                ? ''
                : String(value);

        return div.innerHTML;

    }


    /* ============================================================
       NOTIFICATION
    ============================================================= */

    function showFrontendMessage(message) {

        setText(
            'frontend-info-message',
            message
        );

        openModal('frontendInfoModal');

    }


    /* ============================================================
       COPY
    ============================================================= */

    async function copyText(value, label) {

        const text =
            value === null ||
            value === undefined
                ? ''
                : String(value).trim();

        if (!text) {

            showFrontendMessage(
                `لا توجد بيانات متاحة لنسخ ${label}.`
            );

            return;

        }


        try {

            if (
                navigator.clipboard &&
                window.isSecureContext
            ) {

                await navigator.clipboard.writeText(text);

            } else {

                const textarea =
                    document.createElement('textarea');

                textarea.value = text;
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';

                document.body.appendChild(textarea);

                textarea.focus();
                textarea.select();

                document.execCommand('copy');

                textarea.remove();

            }


            showFrontendMessage(
                `تم نسخ ${label} بنجاح.`
            );

        } catch (error) {

            showFrontendMessage(
                `تعذر نسخ ${label}.`
            );

        }

    }


    /* ============================================================
       CLIENT EDIT
    ============================================================= */

    const clientForm = byId('clientForm');

    function setField(id, value) {

        const field = byId(id);

        if (!field) {
            return;
        }

        field.value =
            value === null ||
            value === undefined
                ? ''
                : value;

    }


    function openClient(id) {

        const client = getClient(id);

        if (!client) {

            showFrontendMessage(
                'لم يتم العثور على بيانات العميل في بيانات الصفحة الحالية.'
            );

            return;

        }


        setField(
            'modal-code',
            client.code
        );

        setField(
            'modal-national-id',
            client.national_id
        );

        setField(
            'modal-loan-type',
            client.loan_type
        );

        setField(
            'modal-status',
            client.status
        );

        setField(
            'modal-phone',
            client.phone
        );

        setField(
            'modal-phone2',
            client.phone2
        );

        setField(
            'modal-email',
            client.email
        );

        setField(
            'modal-governorate',
            client.governorate
        );

        setField(
            'modal-address',
            client.address
        );

        setField(
            'modal-loan-start-date',
            client.loan_start_date
        );

        setField(
            'modal-loan-end-date',
            client.loan_end_date
        );

        setField(
            'modal-last-payment-date',
            client.last_payment_date
        );

        setField(
            'modal-total-debt',
            client.total_debt
        );

        setField(
            'modal-overdue-amount',
            client.overdue_amount
        );

        setField(
            'modal-installment-value',
            client.installment_value
        );

        setField(
            'modal-bucket',
            client.bucket
        );

        setField(
            'modal-dpd',
            client.dpd
        );

        setField(
            'modal-min-installment-diff',
            client.min_installment_diff
        );

        setField(
            'modal-next-due-date',
            client.next_due_date
        );

        setField(
            'modal-late-fee',
            client.late_fee
        );

        setField(
            'modal-employer',
            client.employer
        );

        setField(
            'modal-job-title',
            client.job_title
        );

        setField(
            'modal-work-phone',
            client.work_phone
        );

        setField(
            'modal-work-address',
            client.work_address
        );


        setField(
            'client-modal-id',
            client.id
        );


        if (clientForm) {

            clientForm.action =
                updateUrlTemplate.replace(
                    '__ID__',
                    encodeURIComponent(client.id)
                );

        }


        openModal('clientModal');

    }


    /* ============================================================
       TOOLS
    ============================================================= */

    let activeClientId = null;


    function prepareTools(client) {

        if (!client) {
            return;
        }

        activeClientId = client.id;

        setText(
            'tools-client-name',
            client.name
        );

        setText(
            'tools-client-code',
            client.code
        );

    }


    function openTools(id) {

        const client = getClient(id);

        if (!client) {
            return;
        }

        prepareTools(client);

        openModal('clientToolsModal');

    }


    /* ============================================================
       CLIENT HISTORY
    ============================================================= */

    function openClientHistory(id) {

        const client = getClient(id);

        if (!client) {
            return;
        }

        activeClientId = client.id;

        setText(
            'history-name',
            client.name
        );

        setText(
            'history-code',
            client.code
        );

        setText(
            'history-status',
            client.status
        );

        setText(
            'history-employee',
            client.employee
        );

        openModal('clientHistoryModal');

    }


    /* ============================================================
       PAYMENT HISTORY
    ============================================================= */

    function openPaymentHistory(id) {

        const client = getClient(id);

        if (!client) {
            return;
        }

        activeClientId = client.id;

        setText(
            'payment-history-client-name',
            client.name
        );

        setText(
            'payment-history-client-code',
            client.code
        );

        const debt =
            Number(client.total_debt || 0);

        setText(
            'payment-history-debt',
            'EGP ' +
            new Intl.NumberFormat('en-US', {
                maximumFractionDigits: 2
            }).format(debt)
        );

        openModal('paymentHistoryModal');

    }


    /* ============================================================
       CALL HISTORY
    ============================================================= */

    function openCallHistory(id) {

        const client = getClient(id);

        if (!client) {
            return;
        }

        activeClientId = client.id;

        setText(
            'call-history-client-name',
            client.name
        );

        setText(
            'call-history-client-phone',
            client.phone
        );

        openModal('callHistoryModal');

    }


    /* ============================================================
       PROMISE
    ============================================================= */

    function openPromise(id) {

        const client = getClient(id);

        if (!client) {
            return;
        }

        activeClientId = client.id;

        setText(
            'promise-client-name',
            client.name
        );

        setText(
            'promise-client-code',
            client.code
        );

        const dateField =
            byId('promise-date');

        if (dateField && !dateField.value) {

            const today =
                new Date()
                    .toISOString()
                    .split('T')[0];

            dateField.value = today;

        }

        openModal('promiseModal');

    }


    /* ============================================================
       FRONTEND PAYMENT
    ============================================================= */

    function openPayment(id) {

        const client = getClient(id);

        if (!client) {
            return;
        }

        activeClientId = client.id;

        setText(
            'payment-client-name',
            client.name
        );

        setText(
            'payment-client-code',
            client.code
        );

        const amount =
            byId('frontend-payment-amount');

        const receipt =
            byId('frontend-payment-receipt');

        const note =
            byId('frontend-payment-note');

        if (amount) {
            amount.value = '';
        }

        if (receipt) {
            receipt.value = '';
        }

        if (note) {
            note.value = '';
        }

        openModal('paymentModal');

    }


    /* ============================================================
       CURRENT EMPLOYEE
    ============================================================= */

    function openCurrentEmployee(id) {

        const client = getClient(id);

        if (!client) {
            return;
        }

        activeClientId = client.id;

        setText(
            'current-employee-name',
            client.employee
        );

        setText(
            'current-employee-client',
            client.name
        );

        setText(
            'current-employee-code',
            client.code
        );

        openModal('currentEmployeeModal');

    }


    /* ============================================================
       CHANGE EMPLOYEE
    ============================================================= */

    function openChangeEmployee(id) {

        const client = getClient(id);

        if (!client) {
            return;
        }

        activeClientId = client.id;

        setText(
            'change-employee-client-name',
            client.name
        );

        setText(
            'change-employee-current',
            'الموظف الحالي: ' +
            (client.employee || '-')
        );

        const select =
            byId('change-employee-select');

        if (select) {
            select.value = '';
        }

        const reason =
            byId('change-employee-reason');

        if (reason) {
            reason.value = '';
        }

        openModal('changeEmployeeModal');

    }


    /* ============================================================
       UNASSIGN
    ============================================================= */

    function openUnassign(id) {

        const client = getClient(id);

        if (!client) {
            return;
        }

        activeClientId = client.id;

        setText(
            'unassign-client-name',
            client.name
        );

        openModal('unassignModal');

    }


    /* ============================================================
       EXPORT CLIENT
    ============================================================= */

    function csvEscape(value) {

        const text =
            value === null ||
            value === undefined
                ? ''
                : String(value);

        return '"' +
            text.replace(/"/g, '""') +
            '"';

    }


    function exportClient(id) {

        const client = getClient(id);

        if (!client) {
            return;
        }


        const rows = [

            ['البيان', 'القيمة'],

            ['كود العميل', client.code],
            ['اسم العميل', client.name],
            ['National ID', client.national_id],
            ['الهاتف', client.phone],
            ['الهاتف الثاني', client.phone2],
            ['البريد الإلكتروني', client.email],
            ['المحافظة', client.governorate],
            ['العنوان', client.address],

            ['نوع القرض', client.loan_type],
            ['الشريحة', client.bucket],
            ['الحالة', client.status],

            ['إجمالي المديونية', client.total_debt],
            ['المبلغ المتأخر', client.overdue_amount],
            ['قيمة القسط', client.installment_value],
            ['غرامة التأخر', client.late_fee],
            ['DPD', client.dpd],

            ['الموظف', client.employee],

            ['جهة العمل', client.employer],
            ['الوظيفة', client.job_title],
            ['هاتف العمل', client.work_phone],
            ['عنوان العمل', client.work_address]

        ];


        const csv =
            '\uFEFF' +
            rows
                .map(row =>
                    row
                        .map(csvEscape)
                        .join(',')
                )
                .join('\r\n');


        const blob =
            new Blob(
                [csv],
                {
                    type: 'text/csv;charset=utf-8;'
                }
            );


        const url =
            URL.createObjectURL(blob);


        const link =
            document.createElement('a');

        link.href = url;

        link.download =
            `client-${client.code || client.id}.csv`;

        document.body.appendChild(link);

        link.click();

        link.remove();

        URL.revokeObjectURL(url);

    }


    /* ============================================================
       PRINT CLIENT
    ============================================================= */

    function printClient(id) {

        const client = getClient(id);

        if (!client) {
            return;
        }


        const printWindow =
            window.open(
                '',
                '_blank',
                'width=1000,height=800'
            );


        if (!printWindow) {

            showFrontendMessage(
                'تعذر فتح نافذة الطباعة. تأكد من السماح بالنوافذ المنبثقة.'
            );

            return;

        }


        const moneyValue = function (value) {

            const number =
                Number(value || 0);

            return new Intl.NumberFormat('en-US', {
                maximumFractionDigits: 2
            }).format(number);

        };


        const html = `

<!DOCTYPE html>

<html lang="ar" dir="rtl">

<head>

<meta charset="UTF-8">

<title>ملف العميل - ${escapeHtml(client.name || '')}</title>

<style>

    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        padding: 30px;
        background: #ffffff;
        color: #111418;
        font-family: Arial, Tahoma, sans-serif;
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 2px solid #111418;
        padding-bottom: 18px;
        margin-bottom: 24px;
    }

    .title {
        font-size: 24px;
        font-weight: 700;
    }

    .subtitle {
        margin-top: 6px;
        color: #666;
        font-size: 12px;
    }

    .section {
        margin-bottom: 22px;
    }

    .section-title {
        font-size: 15px;
        font-weight: 700;
        margin-bottom: 10px;
        border-bottom: 1px solid #ddd;
        padding-bottom: 8px;
    }

    .grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }

    .item {
        border: 1px solid #ddd;
        padding: 10px;
        border-radius: 8px;
    }

    .label {
        color: #777;
        font-size: 10px;
        margin-bottom: 5px;
    }

    .value {
        font-size: 13px;
        font-weight: 600;
    }

    .footer {
        margin-top: 30px;
        padding-top: 12px;
        border-top: 1px solid #ddd;
        color: #777;
        font-size: 10px;
    }

    @media print {

        body {
            padding: 15px;
        }

    }

</style>

</head>

<body>

    <div class="header">

        <div>

            <div class="title">
                ملف العميل
            </div>

            <div class="subtitle">
                ${escapeHtml(client.name || '-')}
            </div>

        </div>

        <div>
            <strong>
                ${escapeHtml(client.code || '-')}
            </strong>
        </div>

    </div>


    <div class="section">

        <div class="section-title">
            البيانات الأساسية
        </div>

        <div class="grid">

            <div class="item">
                <div class="label">اسم العميل</div>
                <div class="value">${escapeHtml(client.name || '-')}</div>
            </div>

            <div class="item">
                <div class="label">كود العميل</div>
                <div class="value">${escapeHtml(client.code || '-')}</div>
            </div>

            <div class="item">
                <div class="label">National ID</div>
                <div class="value">${escapeHtml(client.national_id || '-')}</div>
            </div>

            <div class="item">
                <div class="label">الحالة</div>
                <div class="value">${escapeHtml(client.status || '-')}</div>
            </div>

            <div class="item">
                <div class="label">الهاتف</div>
                <div class="value" dir="ltr">${escapeHtml(client.phone || '-')}</div>
            </div>

            <div class="item">
                <div class="label">الهاتف الثاني</div>
                <div class="value" dir="ltr">${escapeHtml(client.phone2 || '-')}</div>
            </div>

            <div class="item">
                <div class="label">المحافظة</div>
                <div class="value">${escapeHtml(client.governorate || '-')}</div>
            </div>

            <div class="item">
                <div class="label">العنوان</div>
                <div class="value">${escapeHtml(client.address || '-')}</div>
            </div>

        </div>

    </div>


    <div class="section">

        <div class="section-title">
            البيانات المالية
        </div>

        <div class="grid">

            <div class="item">
                <div class="label">نوع القرض</div>
                <div class="value">${escapeHtml(client.loan_type || '-')}</div>
            </div>

            <div class="item">
                <div class="label">الشريحة</div>
                <div class="value">${escapeHtml(client.bucket || '-')}</div>
            </div>

            <div class="item">
                <div class="label">إجمالي المديونية</div>
                <div class="value">EGP ${moneyValue(client.total_debt)}</div>
            </div>

            <div class="item">
                <div class="label">المبلغ المتأخر</div>
                <div class="value">EGP ${moneyValue(client.overdue_amount)}</div>
            </div>

            <div class="item">
                <div class="label">قيمة القسط</div>
                <div class="value">EGP ${moneyValue(client.installment_value)}</div>
            </div>

            <div class="item">
                <div class="label">غرامة التأخر</div>
                <div class="value">EGP ${moneyValue(client.late_fee)}</div>
            </div>

            <div class="item">
                <div class="label">DPD</div>
                <div class="value">${escapeHtml(client.dpd || '-')}</div>
            </div>

            <div class="item">
                <div class="label">الموظف</div>
                <div class="value">${escapeHtml(client.employee || '-')}</div>
            </div>

        </div>

    </div>


    <div class="section">

        <div class="section-title">
            بيانات العمل
        </div>

        <div class="grid">

            <div class="item">
                <div class="label">جهة العمل</div>
                <div class="value">${escapeHtml(client.employer || '-')}</div>
            </div>

            <div class="item">
                <div class="label">الوظيفة</div>
                <div class="value">${escapeHtml(client.job_title || '-')}</div>
            </div>

            <div class="item">
                <div class="label">هاتف العمل</div>
                <div class="value">${escapeHtml(client.work_phone || '-')}</div>
            </div>

            <div class="item">
                <div class="label">عنوان العمل</div>
                <div class="value">${escapeHtml(client.work_address || '-')}</div>
            </div>

        </div>

    </div>


    <div class="footer">
        تم إنشاء الملف من نظام التحصيل - ${new Date().toLocaleString('ar-EG')}
    </div>


<script>

    window.onload = function () {

        window.print();

    };

<\/script>

</body>

</html>
`;


        printWindow.document.open();

        printWindow.document.write(html);

        printWindow.document.close();

    }


    /* ============================================================
       SELECTION
    ============================================================= */

    const checkAll =
        byId('check-all');

    const counter =
        byId('selected-count');

    const rowChecks =
        Array.from(
            document.querySelectorAll('.row-check')
        );


    function refreshSelection() {

        const selected =
            rowChecks.filter(
                checkbox => checkbox.checked
            ).length;


        if (counter) {
            counter.textContent = selected;
        }


        if (checkAll) {

            checkAll.checked =
                rowChecks.length > 0 &&
                selected === rowChecks.length;

            checkAll.indeterminate =
                selected > 0 &&
                selected < rowChecks.length;

        }


        document
            .querySelectorAll('[data-needs-selection]')
            .forEach(function (button) {

                button.disabled =
                    selected === 0;

            });

    }


    if (checkAll) {

        checkAll.addEventListener(
            'change',
            function () {

                rowChecks.forEach(function (checkbox) {

                    checkbox.checked =
                        checkAll.checked;

                });

                refreshSelection();

            }
        );

    }


    rowChecks.forEach(function (checkbox) {

        checkbox.addEventListener(
            'change',
            refreshSelection
        );

    });


    refreshSelection();


    /* ============================================================
       SELECTED CLIENTS
    ============================================================= */

    function selectedClientIds() {

        return rowChecks
            .filter(
                checkbox => checkbox.checked
            )
            .map(
                checkbox => checkbox.value
            );

    }


    /* ============================================================
       CLIENT TOOLS FROM MODAL
    ============================================================= */

    document.addEventListener(
        'click',
        function (event) {

            const button =
                event.target.closest(
                    '[data-open-client-tools]'
                );

            if (!button) {
                return;
            }

            const details =
                button.closest('details');

            if (details) {
                details.removeAttribute('open');
            }

            openTools(
                button.dataset.openClientTools
            );

        }
    );


    document.addEventListener(
        'click',
        function (event) {

            if (
                event.target.closest(
                    '[data-open-call-history-from-tools]'
                )
            ) {

                closeModal('clientToolsModal');

                if (activeClientId !== null) {
                    openCallHistory(activeClientId);
                }

            }

        }
    );


    document.addEventListener(
        'click',
        function (event) {

            if (
                event.target.closest(
                    '[data-copy-client-code-from-tools]'
                )
            ) {

                const client =
                    getClient(activeClientId);

                if (client) {
                    copyText(
                        client.code,
                        'كود العميل'
                    );
                }

            }

        }
    );


    document.addEventListener(
        'click',
        function (event) {

            if (
                event.target.closest(
                    '[data-copy-national-id-from-tools]'
                )
            ) {

                const client =
                    getClient(activeClientId);

                if (client) {
                    copyText(
                        client.national_id,
                        'National ID'
                    );
                }

            }

        }
    );


    document.addEventListener(
        'click',
        function (event) {

            if (
                event.target.closest(
                    '[data-copy-phone2-from-tools]'
                )
            ) {

                const client =
                    getClient(activeClientId);

                if (client) {
                    copyText(
                        client.phone2,
                        'الهاتف الثاني'
                    );
                }

            }

        }
    );


    document.addEventListener(
        'click',
        function (event) {

            if (
                event.target.closest(
                    '[data-copy-address-from-tools]'
                )
            ) {

                const client =
                    getClient(activeClientId);

                if (client) {
                    copyText(
                        client.address,
                        'العنوان'
                    );
                }

            }

        }
    );


    document.addEventListener(
        'click',
        function (event) {

            if (
                event.target.closest(
                    '[data-unassign-from-tools]'
                )
            ) {

                closeModal('clientToolsModal');

                if (activeClientId !== null) {
                    openUnassign(activeClientId);
                }

            }

        }
    );


    document.addEventListener(
        'click',
        function (event) {

            if (
                event.target.closest(
                    '[data-export-client-from-tools]'
                )
            ) {

                if (activeClientId !== null) {
                    exportClient(activeClientId);
                }

            }

        }
    );


    document.addEventListener(
        'click',
        function (event) {

            if (
                event.target.closest(
                    '[data-print-client-from-tools]'
                )
            ) {

                if (activeClientId !== null) {
                    printClient(activeClientId);
                }

            }

        }
    );


    /* ============================================================
       MAIN ACTION EVENTS
    ============================================================= */

    document.addEventListener(
        'click',
        function (event) {

            const editButton =
                event.target.closest(
                    '[data-open-client]'
                );

            if (editButton) {

                const details =
                    editButton.closest('details');

                if (details) {
                    details.removeAttribute('open');
                }

                openClient(
                    editButton.dataset.openClient
                );

                return;

            }


            const historyButton =
                event.target.closest(
                    '[data-open-client-history]'
                );

            if (historyButton) {

                const details =
                    historyButton.closest('details');

                if (details) {
                    details.removeAttribute('open');
                }

                openClientHistory(
                    historyButton.dataset.openClientHistory
                );

                return;

            }


            const paymentHistoryButton =
                event.target.closest(
                    '[data-open-payment-history]'
                );

            if (paymentHistoryButton) {

                const details =
                    paymentHistoryButton.closest('details');

                if (details) {
                    details.removeAttribute('open');
                }

                openPaymentHistory(
                    paymentHistoryButton.dataset.openPaymentHistory
                );

                return;

            }


            const promiseButton =
                event.target.closest(
                    '[data-open-promise]'
                );

            if (promiseButton) {

                const details =
                    promiseButton.closest('details');

                if (details) {
                    details.removeAttribute('open');
                }

                openPromise(
                    promiseButton.dataset.openPromise
                );

                return;

            }


            const paymentButton =
                event.target.closest(
                    '[data-open-payment]'
                );

            if (paymentButton) {

                const details =
                    paymentButton.closest('details');

                if (details) {
                    details.removeAttribute('open');
                }

                openPayment(
                    paymentButton.dataset.openPayment
                );

                return;

            }


            const currentEmployeeButton =
                event.target.closest(
                    '[data-open-current-employee]'
                );

            if (currentEmployeeButton) {

                const details =
                    currentEmployeeButton.closest('details');

                if (details) {
                    details.removeAttribute('open');
                }

                openCurrentEmployee(
                    currentEmployeeButton.dataset.openCurrentEmployee
                );

                return;

            }


            const changeEmployeeButton =
                event.target.closest(
                    '[data-change-employee]'
                );

            if (changeEmployeeButton) {

                const details =
                    changeEmployeeButton.closest('details');

                if (details) {
                    details.removeAttribute('open');
                }

                openChangeEmployee(
                    changeEmployeeButton.dataset.changeEmployee
                );

                return;

            }


            const copyCodeButton =
                event.target.closest(
                    '[data-copy-client-code]'
                );

            if (copyCodeButton) {

                const details =
                    copyCodeButton.closest('details');

                if (details) {
                    details.removeAttribute('open');
                }

                copyText(
                    copyCodeButton.dataset.copyClientCode,
                    'كود العميل'
                );

                return;

            }


            const copyValueButton =
                event.target.closest(
                    '[data-copy-value]'
                );

            if (copyValueButton) {

                const details =
                    copyValueButton.closest('details');

                if (details) {
                    details.removeAttribute('open');
                }

                copyText(
                    copyValueButton.dataset.copyValue,
                    copyValueButton.dataset.copyLabel || 'البيانات'
                );

            }

        }
    );


    /* ============================================================
       ASSIGN ONE
    ============================================================= */

    /*
    |--------------------------------------------------------------------------
    | Existing banks/_assign-modal.blade.php handles:
    | data-assign-one
    |
    | We intentionally do not override it here.
    |--------------------------------------------------------------------------
    */


    /* ============================================================
       PROMISE SUBMIT
    ============================================================= */

    const promiseForm =
        byId('promiseForm');


    if (promiseForm) {

        promiseForm.addEventListener(
            'submit',
            function (event) {

                event.preventDefault();

                closeModal('promiseModal');

                showFrontendMessage(
                    'تمت معاينة وعد السداد بنجاح. هذه العملية Frontend فقط ولن يتم حفظها في قاعدة البيانات حالياً.'
                );

            }
        );

    }


    /* ============================================================
       FRONTEND PAYMENT SUBMIT
    ============================================================= */

    const frontendPaymentForm =
        byId('frontendPaymentForm');


    if (frontendPaymentForm) {

        frontendPaymentForm.addEventListener(
            'submit',
            function (event) {

                event.preventDefault();

                closeModal('paymentModal');

                showFrontendMessage(
                    'تمت معاينة بيانات الدفعة بنجاح. هذه العملية Frontend فقط ولن يتم حفظها حالياً.'
                );

            }
        );

    }


    /* ============================================================
       CHANGE EMPLOYEE SUBMIT
    ============================================================= */

    const changeEmployeeForm =
        byId('changeEmployeeForm');


    if (changeEmployeeForm) {

        changeEmployeeForm.addEventListener(
            'submit',
            function (event) {

                event.preventDefault();

                const select =
                    byId('change-employee-select');

                if (!select || !select.value) {

                    showFrontendMessage(
                        'اختر الموظف الجديد أولاً.'
                    );

                    return;

                }


                closeModal('changeEmployeeModal');

                showFrontendMessage(
                    'تم تجهيز تغيير الموظف كواجهة Frontend. لن يتم تغيير الموظف الحقيقي حتى يتم ربط العملية بالـ Backend.'
                );

            }
        );

    }


    /* ============================================================
       UNASSIGN SUBMIT
    ============================================================= */

    const unassignForm =
        byId('unassignForm');


    if (unassignForm) {

        unassignForm.addEventListener(
            'submit',
            function (event) {

                event.preventDefault();

                closeModal('unassignModal');

                showFrontendMessage(
                    'تمت معاينة عملية إلغاء التعيين. لن يتم تعديل الموظف الحالي حتى يتم ربط العملية بالـ Backend.'
                );

            }
        );

    }


    /* ============================================================
       SELECTED EXPORT
    ============================================================= */

    const exportSelectedButton =
        byId('exportSelectedButton');


    if (exportSelectedButton) {

        exportSelectedButton.addEventListener(
            'click',
            function () {

                const ids =
                    selectedClientIds();

                if (!ids.length) {
                    return;
                }


                const rows = [
                    [
                        'كود العميل',
                        'اسم العميل',
                        'National ID',
                        'الهاتف',
                        'الهاتف الثاني',
                        'البنك',
                        'نوع القرض',
                        'الشريحة',
                        'إجمالي المديونية',
                        'المتأخر',
                        'الموظف'
                    ]
                ];


                ids.forEach(function (id) {

                    const client =
                        getClient(id);

                    if (!client) {
                        return;
                    }


                    rows.push([
                        client.code,
                        client.name,
                        client.national_id,
                        client.phone,
                        client.phone2,
                        @json($bank->name),
                        client.loan_type,
                        client.bucket,
                        client.total_debt,
                        client.overdue_amount,
                        client.employee
                    ]);

                });


                const csv =
                    '\uFEFF' +
                    rows
                        .map(row =>
                            row
                                .map(csvEscape)
                                .join(',')
                        )
                        .join('\r\n');


                const blob =
                    new Blob(
                        [csv],
                        {
                            type: 'text/csv;charset=utf-8;'
                        }
                    );


                const url =
                    URL.createObjectURL(blob);


                const link =
                    document.createElement('a');

                link.href = url;

                link.download =
                    `bank-${@json($bank->id)}-selected-clients.csv`;

                document.body.appendChild(link);

                link.click();

                link.remove();

                URL.revokeObjectURL(url);

            }
        );

    }


    /* ============================================================
       SYNC BUTTON
    ============================================================= */

    const syncButton =
        byId('syncClientsButton');


    if (syncButton) {

        syncButton.addEventListener(
            'click',
            function () {

                const original =
                    syncButton.innerHTML;

                syncButton.disabled = true;

                syncButton.innerHTML =
                    '<i class="fa-solid fa-spinner fa-spin"></i>';


                setTimeout(
                    function () {

                        syncButton.disabled = false;

                        syncButton.innerHTML =
                            original;

                        showFrontendMessage(
                            'تم تنفيذ معاينة المزامنة. لا توجد عملية Backend مرتبطة بهذه الواجهة حالياً.'
                        );

                    },
                    500
                );

            }
        );

    }


    /* ============================================================
       ACTIVITY LOG
    ============================================================= */

    const activityLogButton =
        byId('activityLogButton');


    if (activityLogButton) {

        activityLogButton.addEventListener(
            'click',
            function () {

                showFrontendMessage(
                    'سجل العمليات سيتم ربطه لاحقاً بالـ Backend. الواجهة الحالية جاهزة لاستقبال السجل.'
                );

            }
        );

    }


    /* ============================================================
       CLOSE DETAILS WHEN CLICKING OUTSIDE
    ============================================================= */

    document.addEventListener(
        'click',
        function (event) {

            if (
                event.target.closest('details[data-menu]')
            ) {
                return;
            }


            document
                .querySelectorAll('details[data-menu][open]')
                .forEach(function (menu) {

                    if (!menu.contains(event.target)) {
                        menu.removeAttribute('open');
                    }

                });

        }
    );


    /* ============================================================
       ESCAPE
    ============================================================= */

    document.addEventListener(
        'keydown',
        function (event) {

            if (event.key !== 'Escape') {
                return;
            }


            document
                .querySelectorAll('details[data-menu][open]')
                .forEach(function (menu) {

                    menu.removeAttribute('open');

                });

        }
    );


})();
</script>

@endpush
```
