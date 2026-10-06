{{-- resources/views/reports/index.blade.php --}}
@extends('layouts.app')

@section('title', 'التقارير')

@section('content')

    <x-page-header title="التقارير" subtitle="إنشاء وتصدير التقارير بصيغة Excel أو PDF" icon="fa-file-lines">
        <x-slot:actions>
            <a href="{{ route('reports.exports') }}" class="btn btn-outline-info btn-sm"><i class="fa-solid fa-clock-rotate-left text-xs"></i> سجل التصدير</a>
        </x-slot:actions>
    </x-page-header>

    <x-flash />

    <section class="grid grid-cols-1 gap-4 xl:grid-cols-3">

        {{-- New report --}}
        <x-section-card title="تقرير جديد" icon="fa-wand-magic-sparkles" color="brand">
            <form method="POST" action="{{ route('reports.store') }}" class="space-y-4">
                @csrf
                <x-select-field name="type" label="نوع التقرير" :options="$types" placeholder="اختر التقرير..." />
                <x-select-field name="bank" label="البنك" :options="$banks" placeholder="كل البنوك" />

                <div class="grid grid-cols-2 gap-3">
                    <x-form-field name="from" label="من تاريخ" type="date" />
                    <x-form-field name="to" label="إلى تاريخ" type="date" />
                </div>

                <x-select-field name="format" label="الصيغة" :options="$formats" value="xlsx" />

                <button type="submit" class="btn btn-primary w-full"><i class="fa-solid fa-file-export text-xs"></i> إنشاء التقرير</button>
            </form>
        </x-section-card>

        {{-- Latest exports --}}
        <div class="xl:col-span-2">
            <x-panel title="آخر التقارير" icon="fa-clock-rotate-left">
                <div class="table-wrap">
                    <table class="data-table whitespace-nowrap">
                        <thead><tr><th>التقرير</th><th>البنك</th><th>الصيغة</th><th>الحالة</th><th>الوقت</th><th></th></tr></thead>
                        <tbody>
                            @foreach($exports as $export)
                                <tr>
                                    <td class="font-semibold text-fg">{{ $types[$export->type] }}</td>
                                    <td>{{ $export->bank }}</td>
                                    <td><span class="badge badge-neutral uppercase">{{ $export->format }}</span></td>
                                    <td><x-status-badge type="export" :status="$export->status" /></td>
                                    <td>{{ $export->at }}</td>
                                    <td>
                                        @if($export->status === 'completed')
                                            {{-- TODO: real download route --}}
                                            <a href="#" class="btn btn-outline-success btn-sm"><i class="fa-solid fa-download text-xs"></i> تحميل</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <a href="{{ route('reports.exports') }}" class="btn btn-secondary btn-sm mt-4 w-full">عرض سجل التصدير الكامل <i class="fa-solid fa-chevron-left text-[9px]"></i></a>
            </x-panel>
        </div>

    </section>

@endsection