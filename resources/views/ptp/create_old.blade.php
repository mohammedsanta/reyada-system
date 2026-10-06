{{-- resources/views/ptp/create.blade.php --}}
@extends('layouts.app')

@section('title', 'إضافة وعد دفع - ' . $bank->name)

@section('content')

    <x-bank-header :bank="$bank" title="إضافة وعد دفع" subtitle="تسجيل وعد جديد من عميل" :back="route('banks.ptp.index', $bank->id)" />

    <form method="POST" action="{{ route('banks.ptp.store', $bank->id) }}" class="max-w-3xl space-y-4">
        @csrf

        <x-section-card title="بيانات الوعد" icon="fa-handshake" color="warning">
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <x-select-field name="client_code" label="العميل" :options="$clients" :value="$selected" placeholder="اختر العميل..." />
                <x-select-field name="employee_id" label="الموظف الذي أخذ الوعد" :options="$employees" placeholder="اختر الموظف..." />

                <x-form-field name="amount" label="المبلغ الموعود (EGP)" type="number" min="1" step="0.01" placeholder="مثال: 1500" />
                <x-form-field name="promise_date" label="تاريخ السداد الموعود" type="date" :value="$tomorrow" />

                <x-select-field name="method" label="طريقة السداد المتوقعة" :options="$methods" placeholder="غير محددة" />
            </div>

            <div class="mt-3">
                <x-textarea-field name="notes" label="ملاحظات" rows="3" />
            </div>
        </x-section-card>

        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk text-xs"></i> تسجيل الوعد</button>
            <a href="{{ route('banks.ptp.index', $bank->id) }}" class="btn btn-secondary">إلغاء</a>
        </div>
    </form>

@endsection