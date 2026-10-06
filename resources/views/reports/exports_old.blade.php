{{-- resources/views/reports/exports.blade.php : export history --}}
@extends('layouts.app')

@section('title', 'سجل التصدير')

@section('content')

    <x-page-header title="سجل التصدير" subtitle="كل التقارير التي قمت بإنشائها" icon="fa-clock-rotate-left">
        <x-slot:actions>
            <a href="{{ route('reports.index') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus text-xs"></i> تقرير جديد</a>
        </x-slot:actions>
    </x-page-header>

    <x-flash />

    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-metric-card label="كل التقارير" :value="$counts['all']" icon="fa-file-lines" color="info" />
        <x-metric-card label="جاهزة" :value="$counts['completed'] ?? 0" icon="fa-circle-check" color="brand" />
        <x-metric-card label="قيد التجهيز" :value="$counts['pending'] ?? 0" icon="fa-hourglass-half" color="warning" />
        <x-metric-card label="فشلت" :value="$counts['failed'] ?? 0" icon="fa-circle-xmark" color="danger" />
    </section>

    {{-- FILTERS --}}
    <form method="GET" action="{{ route('reports.exports') }}" class="card filter-bar">
        <div class="w-56">
            <label for="type" class="form-label">التقرير</label>
            <select id="type" name="type" class="form-input" onchange="this.form.submit()">
                <option value="">الكل</option>
                @foreach($types as $key => $label) <option value="{{ $key }}" @selected($filters['type'] === $key)>{{ $label }}</option> @endforeach
            </select>
        </div>

        <div class="w-40">
            <label for="status" class="form-label">الحالة</label>
            <select id="status" name="status" class="form-input" onchange="this.form.submit()">
                <option value="">الكل</option>
                @foreach($statuses as $key => $label) <option value="{{ $key }}" @selected($filters['status'] === $key)>{{ $label }}</option> @endforeach
            </select>
        </div>

        <div class="w-40">
            <label for="format" class="form-label">الصيغة</label>
            <select id="format" name="format" class="form-input" onchange="this.form.submit()">
                <option value="">الكل</option>
                @foreach($formats as $key => $label) <option value="{{ $key }}" @selected($filters['format'] === $key)>{{ $label }}</option> @endforeach
            </select>
        </div>

        <a href="{{ route('reports.exports') }}" class="btn btn-secondary" title="إعادة ضبط"><i class="fa-solid fa-rotate-right"></i></a>
    </form>

    {{-- TABLE --}}
    <div class="table-wrap">
        <table class="data-table whitespace-nowrap">
            <thead>
                <tr><th>#</th><th>التقرير</th><th>البنك</th><th>الفترة</th><th>الصيغة</th><th>الصفوف</th><th>الحالة</th><th>أنشأه</th><th>الوقت</th><th>الإجراءات</th></tr>
            </thead>

            <tbody>
                @forelse($exports as $export)
                    <tr>
                        <td>{{ $export->id }}</td>
                        <td class="font-semibold text-fg">{{ $types[$export->type] }}</td>
                        <td>{{ $export->bank }}</td>
                        <td>{{ $export->scope }}</td>
                        <td><span class="badge badge-neutral uppercase">{{ $export->format }}</span></td>
                        <td>{{ $export->rows ?? '-' }}</td>
                        <td>
                            <x-status-badge type="export" :status="$export->status" />
                            @if($export->error) <p class="mt-1 max-w-[220px] whitespace-normal text-[11px] text-danger">{{ $export->error }}</p> @endif
                        </td>
                        <td>{{ $export->user }}</td>
                        <td>{{ $export->at }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                @if($export->status === 'completed')
                                    {{-- TODO: real download route --}}
                                    <a href="#" class="btn btn-outline-success btn-sm"><i class="fa-solid fa-download text-xs"></i> تحميل</a>
                                @elseif($export->status === 'failed')
                                    <form method="POST" action="{{ route('reports.exports.retry', $export->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-info btn-sm"><i class="fa-solid fa-rotate-right text-xs"></i> إعادة المحاولة</button>
                                    </form>
                                @endif

                                @unless($export->status === 'pending')
                                    <form method="POST" action="{{ route('reports.exports.destroy', $export->id) }}" onsubmit="return confirm('هل تريد حذف هذا التقرير؟')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="حذف"><i class="fa-solid fa-trash text-xs"></i></button>
                                    </form>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10"><x-empty-state icon="fa-file-circle-xmark" text="لا توجد تقارير مطابقة" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection