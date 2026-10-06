{{-- resources/views/clients/index.blade.php --}}
@extends('layouts.app')

@section('title', 'العملاء')

@php
    $statusBadge = [
        'ACTIVE'   => ['نشط',   'badge-info'],
        'INACTIVE' => ['متوقف', 'badge-neutral'],
        'PAID'     => ['مسدد',  'badge-success'],
        'LEGAL'    => ['قضائي', 'badge-danger'],
    ];
@endphp

@section('content')

    <x-page-header title="العملاء" subtitle="كل عملاء البنوك في مكان واحد" icon="fa-address-book">
        <x-slot:actions>
            {{-- TODO: real export route --}}
            <a href="#" class="btn btn-outline-success btn-sm"><i class="fa-solid fa-file-excel"></i> إكسل</a>
        </x-slot:actions>
    </x-page-header>

    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($stats as $stat)
            <x-metric-card :label="$stat['label']" :value="$stat['value']" :unit="$stat['unit']" :icon="$stat['icon']" :color="$stat['color']" />
        @endforeach
    </section>

    {{-- FILTERS (GET form: the URL keeps them) --}}
    <form id="filters" method="GET" action="{{ route('clients.index') }}" class="card filter-bar">

        <div class="min-w-[260px] flex-1">
            <label for="search" class="sr-only">بحث</label>
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-dim"></i>
                <input id="search" name="search" type="search" value="{{ $filters['search'] }}" placeholder="الاسم، الرقم القومي، الكود أو الهاتف..." class="form-input pl-9">
            </div>
        </div>

        <div class="w-48">
            <label for="bank" class="form-label">البنك</label>
            <select id="bank" name="bank" class="form-input" onchange="this.form.submit()">
                <option value="">كل البنوك</option>
                @foreach($banks as $id => $name) <option value="{{ $id }}" @selected($filters['bank'] === (string) $id)>{{ $name }}</option> @endforeach
            </select>
        </div>

        <div class="w-40">
            <label for="loan_type" class="form-label">نوع القرض</label>
            <select id="loan_type" name="loan_type" class="form-input" onchange="this.form.submit()">
                <option value="">الكل</option>
                @foreach($loanTypes as $type) <option value="{{ $type }}" @selected($filters['loan_type'] === $type)>{{ $type }}</option> @endforeach
            </select>
        </div>

        <div class="w-36">
            <label for="governorate" class="form-label">المحافظة</label>
            <select id="governorate" name="governorate" class="form-input" onchange="this.form.submit()">
                <option value="">الكل</option>
                @foreach($governorates as $name) <option value="{{ $name }}" @selected($filters['governorate'] === $name)>{{ $name }}</option> @endforeach
            </select>
        </div>

        <div class="w-28">
            <label for="bucket" class="form-label">الشريحة</label>
            <select id="bucket" name="bucket" class="form-input" onchange="this.form.submit()">
                <option value="">الكل</option>
                @foreach($buckets as $bucket) <option value="{{ $bucket }}" @selected($filters['bucket'] === (string) $bucket)>{{ $bucket }}</option> @endforeach
            </select>
        </div>

        <div class="w-32">
            <label for="status" class="form-label">الحالة</label>
            <select id="status" name="status" class="form-input" onchange="this.form.submit()">
                <option value="">الكل</option>
                @foreach($statuses as $status) <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $statusBadge[$status][0] }}</option> @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter text-xs"></i> بحث</button>
        <a href="{{ route('clients.index') }}" class="btn btn-secondary" title="إعادة ضبط"><i class="fa-solid fa-rotate-right"></i></a>
    </form>

    {{-- TABLE --}}
    <div class="table-wrap">
        <table class="data-table whitespace-nowrap">
            <thead>
                <tr>
                    <th>الكود</th><th>العميل</th><th>الرقم القومي</th><th>الهاتف</th><th>المحافظة</th><th>البنك</th>
                    <th>نوع القرض</th><th>الشريحة</th><th>المديونية</th><th>المتبقي</th><th>الموظف</th><th>الحالة</th><th></th>
                </tr>
            </thead>

            <tbody>
                @forelse($clients as $client)
                    <tr>
                        <td><span class="badge badge-info font-mono">{{ $client->code }}</span></td>
                        <td class="font-semibold text-fg">{{ $client->name }}</td>
                        <td><span dir="ltr">{{ $client->national_id }}</span></td>
                        <td class="text-info"><span dir="ltr">{{ $client->phone }}</span></td>
                        <td>{{ $client->governorate }}</td>
                        <td>{{ $client->bank }}</td>
                        <td>{{ $client->loan_type }}</td>
                        <td><span class="badge badge-neutral">{{ $client->bucket }}</span></td>
                        <td class="font-bold text-fg">EGP {{ number_format($client->debt) }}</td>
                        <td @class(['font-semibold', 'text-danger' => $client->remaining > 0, 'text-dim' => $client->remaining <= 0])>EGP {{ number_format($client->remaining) }}</td>
                        <td>@if($client->employee) {{ $client->employee }} @else <span class="text-warning">بدون موظف</span> @endif</td>
                        <td><span class="badge {{ $statusBadge[$client->status][1] }}">{{ $statusBadge[$client->status][0] }}</span></td>
                        <td><a href="{{ $client->url }}" class="btn btn-outline-info btn-sm" title="فتح في صفحة البنك"><i class="fa-solid fa-arrow-up-right-from-square text-xs"></i> فتح</a></td>
                    </tr>
                @empty
                    <tr><td colspan="13"><x-empty-state icon="fa-user-slash" text="لا يوجد عملاء مطابقون للبحث" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- FOOTER: rows per page + pagination --}}
    <div class="mt-4 flex flex-wrap items-center justify-between gap-4 text-xs text-muted">
        <div class="flex items-center gap-3">
            <span>
                @if($clients->total() > 0) عرض {{ $clients->firstItem() }} - {{ $clients->lastItem() }} من {{ $clients->total() }} @else لا توجد نتائج @endif
            </span>

            <label class="flex items-center gap-2">
                عدد الصفوف
                <select name="per_page" form="filters" class="form-input w-20 py-1.5" onchange="this.form.submit()">
                    @foreach($perPageOptions as $option) <option value="{{ $option }}" @selected($filters['per_page'] === $option)>{{ $option }}</option> @endforeach
                </select>
            </label>
        </div>

        <div class="flex items-center gap-3">
            @if($clients->previousPageUrl())
                <a href="{{ $clients->previousPageUrl() }}" class="btn btn-secondary btn-sm"><i class="fa-solid fa-chevron-right text-[10px]"></i> السابق</a>
            @else
                <span class="btn btn-secondary btn-sm" style="opacity:.4;pointer-events:none"><i class="fa-solid fa-chevron-right text-[10px]"></i> السابق</span>
            @endif

            <span>صفحة <b class="text-fg">{{ $clients->currentPage() }}</b> / {{ max(1, $clients->lastPage()) }}</span>

            @if($clients->nextPageUrl())
                <a href="{{ $clients->nextPageUrl() }}" class="btn btn-secondary btn-sm">التالي <i class="fa-solid fa-chevron-left text-[10px]"></i></a>
            @else
                <span class="btn btn-secondary btn-sm" style="opacity:.4;pointer-events:none">التالي <i class="fa-solid fa-chevron-left text-[10px]"></i></span>
            @endif
        </div>
    </div>

@endsection