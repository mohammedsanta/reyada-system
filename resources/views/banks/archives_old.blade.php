{{-- resources/views/banks/archives.blade.php --}}
@extends('layouts.app')

@section('title', 'النطاقات المؤرشفة - ' . $bank->name)

@section('content')

    <x-bank-header :bank="$bank" title="النطاقات المؤرشفة" subtitle="استعراض الأشهر المغلقة (للقراءة فقط)" />

    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($stats as $stat)
            <x-metric-card :label="$stat['label']" :value="$stat['value']" :unit="$stat['unit']" :icon="$stat['icon']" :color="$stat['color']" />
        @endforeach
    </section>

    <form method="GET" action="{{ route('banks.archives.index', $bank->id) }}" class="card filter-bar">
        <div class="w-44">
            <label for="year" class="form-label">السنة</label>
            <select id="year" name="year" class="form-input" onchange="this.form.submit()">
                <option value="">كل السنوات</option>
                @foreach($years as $y)
                    <option value="{{ $y }}" @selected($year === (string) $y)>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <a href="{{ route('banks.archives.index', $bank->id) }}" class="btn btn-secondary" title="إعادة ضبط"><i class="fa-solid fa-rotate-right"></i></a>
    </form>

    <div class="table-wrap">
        <table class="data-table whitespace-nowrap">
            <thead>
                <tr><th>الشهر</th><th>الحالات</th><th>المديونية</th><th>المحصل</th><th>المتبقي</th><th class="w-44">نسبة التحصيل</th><th>أرشفه</th><th>الملف</th><th>الإجراءات</th></tr>
            </thead>

            <tbody>
                @forelse($archives as $archive)
                    @php $barClass = $archive->rate >= 50 ? 'bg-brand' : ($archive->rate >= 30 ? 'bg-warning' : 'bg-danger'); @endphp
                    <tr>
                        <td>
                            <span class="font-bold text-fg">{{ $archive->label }}</span>
                            <span class="badge badge-neutral mr-2"><i class="fa-solid fa-lock ml-1 text-[9px]"></i> مؤرشف</span>
                        </td>
                        <td>{{ $archive->cases }}</td>
                        <td>EGP {{ number_format($archive->debt) }}</td>
                        <td class="font-semibold text-brand">EGP {{ number_format($archive->collected) }}</td>
                        <td>EGP {{ number_format($archive->remaining) }}</td>
                        <td>
                            <div class="mb-1 text-[11px] text-muted">{{ $archive->rate }}%</div>
                            <div class="progress"><div class="h-full rounded-full {{ $barClass }}" style="width: {{ $archive->rate }}%"></div></div>
                        </td>
                        <td><span class="text-fg">{{ $archive->by }}</span><br><span class="text-[11px] text-dim">{{ $archive->at }}</span></td>
                        <td><span class="font-mono text-xs" dir="ltr">{{ $archive->file }}</span></td>
                        <td>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('banks.archives.show', [$bank->id, $archive->id]) }}" class="btn btn-outline-info btn-sm"><i class="fa-solid fa-eye text-xs"></i> عرض</a>
                                {{-- TODO: real download route --}}
                                <a href="#" class="btn btn-outline-success btn-sm" title="تحميل Excel"><i class="fa-solid fa-file-excel text-xs"></i></a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9"><x-empty-state icon="fa-box-archive" text="لا توجد نطاقات مؤرشفة" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection