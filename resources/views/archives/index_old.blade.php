{{-- resources/views/archives/index.blade.php --}}
@extends('layouts.app')

@section('title', 'المستودعات الشهرية')

@section('content')

    <x-page-header title="المستودعات الشهرية" subtitle="ملخصات الأشهر المغلقة لكل البنوك (للقراءة فقط)" icon="fa-box-archive" />

    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($stats as $stat)
            <x-metric-card :label="$stat['label']" :value="$stat['value']" :unit="$stat['unit']" :icon="$stat['icon']" :color="$stat['color']" />
        @endforeach
    </section>

    <form method="GET" action="{{ route('archives.index') }}" class="card filter-bar">
        <div class="w-40">
            <label for="year" class="form-label">السنة</label>
            <select id="year" name="year" class="form-input" onchange="this.form.submit()">
                <option value="">الكل</option>
                @foreach($years as $year)
                    <option value="{{ $year }}" @selected($filters['year'] === (string) $year)>{{ $year }}</option>
                @endforeach
            </select>
        </div>

        <div class="w-56">
            <label for="bank" class="form-label">البنك</label>
            <select id="bank" name="bank" class="form-input" onchange="this.form.submit()">
                <option value="">كل البنوك</option>
                @foreach($banks as $id => $name)
                    <option value="{{ $id }}" @selected($filters['bank'] === (string) $id)>{{ $name }}</option>
                @endforeach
            </select>
        </div>

        <a href="{{ route('archives.index') }}" class="btn btn-secondary" title="إعادة ضبط"><i class="fa-solid fa-rotate-right"></i></a>
    </form>

    <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse($archives as $archive)
            @php $barClass = $archive->rate >= 50 ? 'bg-brand' : ($archive->rate >= 30 ? 'bg-warning' : 'bg-danger'); @endphp

            <div class="card">
                <div class="mb-4 flex items-start justify-between gap-2">
                    <div>
                        <h3 class="text-sm font-bold text-fg">{{ $archive->month_label }}</h3>
                        <a href="{{ $archive->bank_url }}" class="mt-1 inline-flex items-center gap-1 text-xs text-muted transition hover:text-brand">
                            <i class="fa-solid fa-building-columns text-info"></i> {{ $archive->bank }}
                        </a>
                    </div>
                    <span class="badge badge-neutral"><i class="fa-solid fa-lock ml-1 text-[9px]"></i> مؤرشف</span>
                </div>

                <div class="grid grid-cols-3 gap-2 text-center">
                    <div><div class="text-[11px] text-dim">الحالات</div><div class="mt-1 text-sm font-bold text-fg">{{ $archive->cases }}</div></div>
                    <div><div class="text-[11px] text-dim">المديونية</div><div class="mt-1 text-sm font-bold text-fg">{{ number_format($archive->debt / 1000) }}k</div></div>
                    <div><div class="text-[11px] text-dim">المحصل</div><div class="mt-1 text-sm font-bold text-brand">{{ number_format($archive->collected / 1000) }}k</div></div>
                </div>

                <div class="mt-4">
                    <div class="mb-1 flex justify-between text-[11px] text-muted"><span>نسبة التحصيل</span><span>{{ $archive->rate }}%</span></div>
                    <div class="progress"><div class="h-full rounded-full {{ $barClass }}" style="width: {{ $archive->rate }}%"></div></div>
                </div>

                <div class="mt-4 flex items-center justify-between gap-2 border-t border-line pt-3">
                    <span class="text-[11px] text-dim">{{ $archive->by }} · {{ $archive->at }}</span>

                    <div class="flex items-center gap-2">
                        <a href="{{ $archive->url }}" class="btn btn-outline-info btn-sm"><i class="fa-solid fa-eye text-xs"></i> عرض</a>
                        {{-- TODO: real download route --}}
                        <a href="#" class="btn btn-outline-success btn-sm" title="تحميل Excel"><i class="fa-solid fa-file-excel text-xs"></i></a>
                    </div>
                </div>
            </div>
        @empty
            <div class="card md:col-span-2 xl:col-span-3"><x-empty-state icon="fa-box-archive" text="لا توجد مستودعات مطابقة" /></div>
        @endforelse
    </section>

@endsection