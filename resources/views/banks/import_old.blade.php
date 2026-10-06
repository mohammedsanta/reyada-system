{{-- resources/views/banks/import.blade.php --}}
@extends('layouts.app')

@section('title', 'استيراد النطاق - ' . $bank->name)

@section('content')

    <x-bank-header :bank="$bank" title="استيراد النطاق" subtitle="رفع ملف Excel وتحديث الحالات">
        <x-slot:actions>
            {{-- TODO: link to a real template file --}}
            <a href="#" class="btn btn-outline-success btn-sm"><i class="fa-solid fa-file-excel"></i> تحميل القالب</a>
        </x-slot:actions>
    </x-bank-header>

    <x-flash />

    <section class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-3">

        {{-- UPLOAD FORM --}}
        <form method="POST" action="{{ route('banks.scope.import.store', $bank->id) }}" enctype="multipart/form-data" class="space-y-4 xl:col-span-2">
            @csrf

            <x-section-card title="بيانات الاستيراد" icon="fa-file-arrow-up" color="cyan">
                <div class="grid grid-cols-2 gap-3">
                    <x-select-field name="month" label="الشهر" :options="$months" :value="$defaults['month']" />
                    <x-select-field name="year" label="السنة" :options="$years" :value="$defaults['year']" />
                </div>

                <div class="mt-4">
                    <input id="file" name="file" type="file" accept=".xlsx,.xls,.csv" class="sr-only">

                    <label for="file" class="dropzone">
                        <i class="fa-solid fa-cloud-arrow-up text-3xl text-cyan"></i>
                        <span id="file-name" class="font-semibold text-fg">اضغط لاختيار الملف</span>
                        <span class="text-[11px] text-dim">xlsx, xls, csv · حتى 10 ميجابايت</span>
                    </label>

                    @error('file') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </x-section-card>

            <x-section-card title="التعامل مع البيانات الموجودة" icon="fa-sliders" color="warning">
                <div class="space-y-2">
                    @foreach($modes as $key => $mode)
                        <label class="check-row">
                            <input type="radio" name="mode" value="{{ $key }}" class="accent-brand" @checked(old('mode', 'upsert') === $key)>
                            <span>
                                <b class="text-fg">{{ $mode['label'] }}</b>
                                <span class="block text-[11px] text-dim">{{ $mode['hint'] }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>

                @error('mode') <p class="form-error">{{ $message }}</p> @enderror
            </x-section-card>

            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-play text-xs"></i> بدء الاستيراد</button>
                <a href="{{ route('banks.panel', $bank->id) }}" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>

        {{-- REQUIRED COLUMNS --}}
        <x-section-card title="أعمدة الملف" icon="fa-table-columns" color="info">
            <p class="mb-2 text-xs font-semibold text-fg">أعمدة مطلوبة</p>
            <div class="mb-4 flex flex-wrap gap-1.5">
                @foreach($required as $column)
                    <span class="badge badge-warning font-mono" dir="ltr">{{ $column }} *</span>
                @endforeach
            </div>

            <p class="mb-2 text-xs font-semibold text-fg">أعمدة اختيارية</p>
            <div class="flex flex-wrap gap-1.5">
                @foreach($optional as $column)
                    <span class="tag font-mono" dir="ltr">{{ $column }}</span>
                @endforeach
            </div>

            <p class="mt-4 text-[11px] leading-5 text-dim">
                الصف الأول في الملف يجب أن يحتوي على أسماء الأعمدة كما هي بالإنجليزية. التواريخ بصيغة YYYY-MM-DD.
            </p>
        </x-section-card>

    </section>

    {{-- HISTORY --}}
    <x-panel title="سجل عمليات الاستيراد" icon="fa-clock-rotate-left">
        <div class="table-wrap">
            <table class="data-table whitespace-nowrap">
                <thead>
                    <tr><th>الملف</th><th>الفترة</th><th>المنفذ</th><th>الصفوف</th><th>نجح</th><th>فشل</th><th>الحالة</th><th>الوقت</th></tr>
                </thead>
                <tbody>
                    @forelse($history as $row)
                        <tr>
                            <td class="font-semibold text-fg"><span dir="ltr">{{ $row->file }}</span></td>
                            <td>{{ $row->period }}</td>
                            <td>{{ $row->by }}</td>
                            <td>{{ $row->total }}</td>
                            <td class="text-brand">{{ $row->success }}</td>
                            <td @class(['text-danger' => $row->failed > 0])>{{ $row->failed }}</td>
                            <td><x-status-badge type="import" :status="$row->status" /></td>
                            <td>{{ $row->at }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><x-empty-state text="لا توجد عمليات استيراد سابقة" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-panel>

@endsection

@push('scripts')
    <script>
        // show the chosen file name inside the dropzone
        document.getElementById('file').addEventListener('change', (event) => {
            const file = event.target.files[0];
            document.getElementById('file-name').textContent = file ? file.name : 'اضغط لاختيار الملف';
        });
    </script>
@endpush