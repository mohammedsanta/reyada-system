{{-- resources/views/banks/archive.blade.php : one archived scope (read-only) --}}
@extends('layouts.app')

@section('title', 'نطاق ' . $archive->label . ' - ' . $bank->name)

@php
    $caseStatus = [
        'active' => ['نشط', 'badge-info'],
        'paid'   => ['مسدد', 'badge-success'],
        'legal'  => ['قضائي', 'badge-danger'],
    ];
@endphp

@section('content')

    <x-bank-header :bank="$bank" :title="'نطاق ' . $archive->label" subtitle="مؤرشف (للقراءة فقط)" :back="route('banks.archives.index', $bank->id)">
        <x-slot:actions>
            {{-- TODO: real download route --}}
            <a href="#" class="btn btn-outline-success btn-sm"><i class="fa-solid fa-file-excel"></i> تحميل Excel</a>
        </x-slot:actions>
    </x-bank-header>

    {{-- read-only notice --}}
    <div class="mb-6 flex items-center gap-3 rounded-xl border border-warning/20 bg-warning/[0.05] px-4 py-3 text-sm text-warning">
        <i class="fa-solid fa-lock"></i>
        <span>هذا النطاق مؤرشف منذ {{ $archive->at }} بواسطة {{ $archive->by }}، ولا يمكن تعديل بياناته أو توزيعه.</span>
    </div>

    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-metric-card label="عدد الحالات" :value="$archive->cases" icon="fa-briefcase" color="info" />
        <x-metric-card label="إجمالي المديونية" :value="number_format($archive->debt)" unit="EGP" icon="fa-wallet" color="accent" />
        <x-metric-card label="المحصل" :value="number_format($archive->collected)" unit="EGP" icon="fa-money-bill-wave" color="brand" />
        <x-metric-card label="نسبة التحصيل" :value="$archive->rate . '%'" icon="fa-bullseye" color="cyan" />
    </section>

    <form method="GET" action="{{ route('banks.archives.show', [$bank->id, $archive->id]) }}" class="card filter-bar">
        <div class="min-w-[240px] flex-1">
            <label for="search" class="sr-only">بحث</label>
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-dim"></i>
                <input id="search" name="search" type="search" value="{{ $search }}" placeholder="ابحث بالاسم أو كود العميل..." class="form-input pl-9">
            </div>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass text-xs"></i> بحث</button>
        <a href="{{ route('banks.archives.show', [$bank->id, $archive->id]) }}" class="btn btn-secondary" title="إعادة ضبط"><i class="fa-solid fa-rotate-right"></i></a>
    </form>

    <div class="table-wrap">
        <table class="data-table whitespace-nowrap">
            <thead>
                <tr><th>كود العميل</th><th>اسم العميل</th><th>الهاتف</th><th>المديونية</th><th>المحصل</th><th>المتبقي</th><th>الحالة النهائية</th><th>الموظف</th></tr>
            </thead>

            <tbody>
                @forelse($cases as $case)
                    <tr>
                        <td><span class="badge badge-info font-mono">{{ $case->code }}</span></td>
                        <td class="font-semibold text-fg">{{ $case->name }}</td>
                        <td><span dir="ltr">{{ $case->phone }}</span></td>
                        <td>EGP {{ number_format($case->debt) }}</td>
                        <td class="font-semibold text-brand">EGP {{ number_format($case->collected) }}</td>
                        <td @class(['text-danger' => $case->remaining > 0])>EGP {{ number_format($case->remaining) }}</td>
                        <td><span class="badge {{ $caseStatus[$case->status][1] }}">{{ $caseStatus[$case->status][0] }}</span></td>
                        <td>{{ $case->employee }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8"><x-empty-state text="لا توجد حالات مطابقة" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="mt-3 text-[11px] text-dim">عرض {{ $cases->count() }} حالات (عينة تجريبية من {{ $archive->cases }}).</p>

@endsection