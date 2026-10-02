{{-- resources/views/banks/complaint.blade.php : one complaint --}}
@extends('layouts.app')

@section('title', 'شكوى ' . $complaint->reference)

@section('content')

    <x-bank-header :bank="$bank" :title="'شكوى ' . $complaint->reference" :subtitle="$complaint->subject" :back="route('banks.complaints.index', $bank->id)" />

    <x-flash />

    <section class="grid grid-cols-1 gap-4 xl:grid-cols-3">

        <div class="space-y-4 xl:col-span-2">

            <x-section-card title="تفاصيل الشكوى" icon="fa-envelope-open-text" color="warning">
                <div class="mb-4 flex flex-wrap items-center gap-2">
                    <x-status-badge type="complaint" :status="$complaint->status" />
                    <x-status-badge type="priority" :status="$complaint->priority" />
                    <span class="badge badge-neutral">{{ $sources[$complaint->source] }}</span>
                </div>

                <h2 class="text-base font-bold text-fg">{{ $complaint->subject }}</h2>
                <p class="mt-3 text-sm leading-7 text-muted">{{ $complaint->description }}</p>

                @if($complaint->resolution)
                    <div class="mt-5 rounded-xl border border-brand/20 bg-brand/[0.04] p-4">
                        <p class="mb-1 text-xs font-bold text-brand"><i class="fa-solid fa-circle-check ml-1"></i> نتيجة الشكوى</p>
                        <p class="text-sm text-fg">{{ $complaint->resolution }}</p>
                    </div>
                @endif
            </x-section-card>

            <x-section-card title="سجل الشكوى" icon="fa-clock-rotate-left" color="cyan">
                <ol class="space-y-3 text-sm">
                    <li class="flex items-start gap-3"><i class="fa-solid fa-circle mt-1.5 text-[7px] text-brand"></i><span class="text-fg">تم تسجيل الشكوى <span class="text-[11px] text-dim">· {{ $complaint->created }}</span></span></li>
                    <li class="flex items-start gap-3"><i class="fa-solid fa-circle mt-1.5 text-[7px] text-info"></i><span class="text-fg">أُسندت إلى {{ $complaint->assigned_name }} <span class="text-[11px] text-dim">· {{ $complaint->created }}</span></span></li>
                    @if($complaint->status === 'resolved' || $complaint->status === 'rejected')
                        <li class="flex items-start gap-3"><i class="fa-solid fa-circle mt-1.5 text-[7px] text-warning"></i><span class="text-fg">تم إغلاق الشكوى</span></li>
                    @endif
                </ol>
            </x-section-card>
        </div>

        <div class="space-y-4">

            <x-section-card title="العميل" icon="fa-user" color="info">
                <p class="text-sm font-bold text-fg">{{ $complaint->client_name }}</p>
                <p class="mt-1 text-xs text-muted"><span class="badge badge-info font-mono">{{ $complaint->client_code }}</span></p>
                <p class="mt-3 text-sm text-muted"><i class="fa-solid fa-phone ml-1 text-dim"></i><span dir="ltr">{{ $complaint->client_phone }}</span></p>
            </x-section-card>

            <x-section-card title="معالجة الشكوى" icon="fa-pen-to-square" color="brand">
                <form method="POST" action="{{ route('banks.complaints.update', [$bank->id, $complaint->id]) }}" class="space-y-3">
                    @csrf
                    @method('PUT')

                    <x-select-field name="status" label="الحالة" :options="$statuses" :value="$complaint->status" />
                    <x-select-field name="priority" label="الأولوية" :options="$priorities" :value="$complaint->priority" />
                    <x-select-field name="assigned_to" label="المسؤول" :options="$employees" :value="$complaint->assigned_id" placeholder="بدون إسناد" />
                    <x-textarea-field name="resolution" label="نتيجة الشكوى" :value="$complaint->resolution" rows="3" />

                    <button type="submit" class="btn btn-primary w-full"><i class="fa-solid fa-floppy-disk text-xs"></i> حفظ</button>
                </form>
                <p class="mt-3 text-[11px] text-dim">الموعد النهائي للرد: {{ $complaint->due }}</p>
            </x-section-card>

        </div>
    </section>

@endsection