{{-- resources/views/banks/complaints.blade.php --}}
@extends('layouts.app')

@section('title', 'إدارة الشكاوى - ' . $bank->name)

@section('content')

    <x-bank-header :bank="$bank" title="إدارة الشكاوى" subtitle="تسجيل ومتابعة شكاوى العملاء">
        <x-slot:actions>
            <button type="button" class="btn btn-primary btn-sm" data-open-modal="complaintModal"><i class="fa-solid fa-plus text-xs"></i> تسجيل شكوى</button>
        </x-slot:actions>
    </x-bank-header>

    <x-flash />

    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-metric-card label="شكاوى مفتوحة" :value="$counts['open'] ?? 0" icon="fa-envelope-open-text" color="warning" />
        <x-metric-card label="قيد المراجعة" :value="$counts['in_review'] ?? 0" icon="fa-hourglass-half" color="info" />
        <x-metric-card label="تم حلها" :value="$counts['resolved'] ?? 0" icon="fa-circle-check" color="brand" />
        <x-metric-card label="عاجلة" :value="$urgent" icon="fa-triangle-exclamation" color="danger" />
    </section>

    {{-- Tabs + search --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div class="segmented">
            <a href="{{ route('banks.complaints.index', ['bank' => $bank->id, 'search' => $search]) }}" class="segmented-item {{ $status === '' ? 'segmented-item-active' : '' }}">
                الكل <span class="text-[10px] opacity-70">({{ $counts['all'] }})</span>
            </a>
            @foreach($statuses as $key => $label)
                <a href="{{ route('banks.complaints.index', ['bank' => $bank->id, 'status' => $key, 'search' => $search]) }}" class="segmented-item {{ $status === $key ? 'segmented-item-active' : '' }}">
                    {{ $label }} <span class="text-[10px] opacity-70">({{ $counts[$key] ?? 0 }})</span>
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('banks.complaints.index', $bank->id) }}" class="relative w-72">
            <input type="hidden" name="status" value="{{ $status }}">
            <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-dim"></i>
            <input name="search" type="search" value="{{ $search }}" placeholder="رقم الشكوى أو اسم العميل..." class="form-input pl-9" aria-label="بحث">
        </form>
    </div>

    <div class="table-wrap">
        <table class="data-table whitespace-nowrap">
            <thead>
                <tr><th>رقم الشكوى</th><th>العميل</th><th>الموضوع</th><th>الأولوية</th><th>الحالة</th><th>المسؤول</th><th>التسجيل</th><th>الموعد النهائي</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($complaints as $complaint)
                    <tr>
                        <td><span class="badge badge-info font-mono">{{ $complaint->reference }}</span></td>
                        <td class="font-semibold text-fg">{{ $complaint->client_name }} <span class="text-[11px] text-dim">({{ $complaint->client_code }})</span></td>
                        <td class="whitespace-normal min-w-[220px] text-fg">{{ $complaint->subject }}</td>
                        <td><x-status-badge type="priority" :status="$complaint->priority" /></td>
                        <td><x-status-badge type="complaint" :status="$complaint->status" /></td>
                        <td>{{ $complaint->assigned_name }}</td>
                        <td>{{ $complaint->created }}</td>
                        <td @class(['text-danger' => $complaint->due === 'اليوم'])>{{ $complaint->due }}</td>
                        <td><a href="{{ route('banks.complaints.show', [$bank->id, $complaint->id]) }}" class="btn btn-secondary btn-sm">عرض</a></td>
                    </tr>
                @empty
                    <tr><td colspan="9"><x-empty-state icon="fa-headset" text="لا توجد شكاوى مطابقة" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- NEW COMPLAINT --}}
    <x-modal id="complaintModal" title="تسجيل شكوى جديدة" width="46rem">
        <form method="POST" action="{{ route('banks.complaints.store', $bank->id) }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <x-select-field name="client_code" label="العميل" :options="$clients" placeholder="اختر العميل..." />
                <x-select-field name="assigned_to" label="إسناد إلى" :options="$employees" placeholder="بدون إسناد" />
                <x-select-field name="priority" label="الأولوية" :options="$priorities" value="medium" />
                <x-select-field name="source" label="مصدر الشكوى" :options="$sources" placeholder="اختر المصدر..." />
            </div>

            <x-form-field name="subject" label="الموضوع" placeholder="عنوان مختصر للشكوى" />
            <x-textarea-field name="description" label="تفاصيل الشكوى" rows="4" />

            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">تسجيل الشكوى</button>
                <button type="button" class="btn btn-secondary" data-close-modal>إلغاء</button>
            </div>
        </form>
    </x-modal>

@endsection

@push('scripts')
    @if($errors->any())
        <script>document.getElementById('complaintModal').showModal();</script>   {{-- validation failed: show the form again with its errors --}}
    @endif
@endpush