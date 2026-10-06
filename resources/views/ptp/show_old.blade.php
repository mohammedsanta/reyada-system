{{-- resources/views/ptp/show.blade.php : one promise to pay --}}
@extends('layouts.app')

@section('title', 'وعد دفع #' . $promise->id)

@php
    $dueTone = ['muted' => 'text-muted', 'warning' => 'text-warning', 'danger' => 'text-danger', 'brand' => 'text-brand', 'info' => 'text-info'];
    $barTone = ['warning' => 'bg-warning', 'cyan' => 'bg-cyan', 'brand' => 'bg-brand', 'info' => 'bg-info', 'danger' => 'bg-danger'];
@endphp

@section('content')

    <x-bank-header :bank="$bank" :title="'وعد دفع #' . $promise->id" :subtitle="$promise->client_name" :back="route('banks.ptp.index', $bank->id)" />

    <x-flash />

    <section class="grid grid-cols-1 gap-4 xl:grid-cols-3">

        <div class="space-y-4 xl:col-span-2">

            {{-- Promise --}}
            <x-section-card title="تفاصيل الوعد" icon="fa-handshake" color="warning">
                <div class="mb-5 flex flex-wrap items-center gap-3">
                    <x-status-badge type="ptp" :status="$promise->status" />
                    <span class="text-sm font-semibold {{ $dueTone[$promise->due_tone] ?? 'text-muted' }}">{{ $promise->due_label }}</span>
                </div>

                <dl class="grid grid-cols-2 gap-5 text-sm md:grid-cols-4">
                    <div><dt class="text-[11px] text-dim">المبلغ الموعود</dt><dd class="mt-1 text-lg font-bold text-fg">EGP {{ number_format($promise->amount) }}</dd></div>
                    <div><dt class="text-[11px] text-dim">المسدد</dt><dd class="mt-1 text-lg font-bold text-brand">EGP {{ number_format($promise->paid) }}</dd></div>
                    <div><dt class="text-[11px] text-dim">المتبقي</dt><dd class="mt-1 text-lg font-bold {{ $remaining > 0 ? 'text-danger' : 'text-fg' }}">EGP {{ number_format($remaining) }}</dd></div>
                    <div><dt class="text-[11px] text-dim">تاريخ السداد</dt><dd class="mt-1 text-lg font-bold text-fg">{{ $promise->promise_date_label }}</dd></div>
                </dl>

                <div class="mt-5">
                    <div class="mb-1 flex justify-between text-[11px] text-muted"><span>نسبة السداد</span><span>{{ $promise->paid_percent }}%</span></div>
                    <div class="progress"><div class="h-full rounded-full {{ $barTone[$color] }}" style="width: {{ $promise->paid_percent }}%"></div></div>
                </div>

                @if($promise->notes)
                    <div class="mt-5 rounded-xl border border-line bg-white/[0.02] p-4">
                        <p class="mb-1 text-[11px] text-dim">ملاحظات</p>
                        <p class="text-sm leading-6 text-fg">{{ $promise->notes }}</p>
                    </div>
                @endif
            </x-section-card>

            {{-- Timeline --}}
            <x-section-card title="سجل الوعد" icon="fa-clock-rotate-left" color="cyan">
                <ol class="space-y-3 text-sm">
                    <li class="flex items-start gap-3"><i class="fa-solid fa-circle mt-1.5 text-[7px] text-brand"></i><span class="text-fg">سجّل {{ $promise->employee }} الوعد <span class="text-[11px] text-dim">· {{ $promise->created_at_label }}</span></span></li>
                    @if($promise->status !== 'active')
                        <li class="flex items-start gap-3"><i class="fa-solid fa-circle mt-1.5 text-[7px] text-info"></i><span class="text-fg">تمت مراجعة الوعد</span></li>
                    @endif
                    @if(in_array($promise->status, ['kept', 'partial', 'broken'], true))
                        <li class="flex items-start gap-3"><i class="fa-solid fa-circle mt-1.5 text-[7px] text-warning"></i><span class="text-fg">أُغلق الوعد: {{ $statuses[$promise->status] }}</span></li>
                    @endif
                </ol>
            </x-section-card>
        </div>

        <div class="space-y-4">

            {{-- Client --}}
            <x-section-card title="العميل" icon="fa-user" color="info">
                <p class="text-sm font-bold text-fg">{{ $promise->client_name }}</p>
                <p class="mt-1"><span class="badge badge-info font-mono">{{ $promise->client_code }}</span></p>
                <p class="mt-3 text-sm text-muted"><i class="fa-solid fa-phone ml-1 text-dim"></i><a href="tel:{{ $client->phone }}" dir="ltr" class="hover:text-fg">{{ $client->phone }}</a></p>
                <p class="mt-2 text-xs leading-5 text-dim">{{ $client->address }}</p>
                <p class="mt-3 text-xs text-muted"><i class="fa-solid fa-user-tie ml-1 text-dim"></i>الموظف: {{ $promise->employee }}</p>
            </x-section-card>

            {{-- Close / update --}}
            <x-section-card title="تحديث الوعد" icon="fa-pen-to-square" color="brand">
                <form id="promiseForm" method="POST" action="{{ route('banks.ptp.update', [$bank->id, $promise->id]) }}" class="space-y-3" data-amount="{{ $promise->amount }}">
                    @csrf
                    @method('PUT')

                    <x-select-field name="status" label="الحالة" :options="$statuses" :value="$promise->status" />
                    <x-form-field name="paid_amount" label="المبلغ المسدد (EGP)" type="number" min="0" max="{{ $promise->amount }}" step="0.01" :value="$promise->paid" />
                    <x-textarea-field name="notes" label="ملاحظات" :value="$promise->notes" rows="3" />

                    <button type="submit" class="btn btn-primary w-full"><i class="fa-solid fa-floppy-disk text-xs"></i> حفظ</button>
                </form>
            </x-section-card>
        </div>

    </section>

@endsection

@push('scripts')
    <script>
        // "محقق كلياً" fills the full amount automatically, so the two fields can never disagree
        const form   = document.getElementById('promiseForm');
        const status = document.getElementById('status');
        const paid   = document.getElementById('paid_amount');

        status.addEventListener('change', () => {
            if (status.value === 'kept') paid.value = form.dataset.amount;
            if (status.value === 'active' || status.value === 'review') paid.value = 0;
        });
    </script>
@endpush