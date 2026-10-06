{{-- resources/views/installment-companies/index.blade.php --}}
@extends('layouts.app')

@section('title', 'شركات التقسيط')

@section('content')

    <x-page-header title="شركات التقسيط" subtitle="إدارة شركات التقسيط المسجلة" icon="fa-building">
        <x-slot:actions>
            <a href="{{ route('installment-companies.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus text-xs"></i> إضافة شركة</a>
        </x-slot:actions>
    </x-page-header>

    <x-flash />

    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>#</th><th>الشركة</th><th>الكود</th><th>الحالات</th><th>إجمالي المديونية</th><th>الحالة</th><th>إجراءات</th></tr></thead>
            <tbody>
                @forelse($companies as $company)
                    <tr>
                        <td>{{ $company->id }}</td>
                        <td>
                            <a href="{{ route('installment-companies.panel', $company->id) }}" class="group inline-flex items-center gap-3">
                                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-info/15 text-info"><i class="fa-solid fa-building text-sm"></i></span>
                                <span class="font-semibold text-fg transition group-hover:text-brand">{{ $company->name }}</span>
                            </a>
                        </td>
                        <td>{{ $company->code }}</td>
                        <td>{{ $company->cases_count }}</td>
                        <td>EGP {{ number_format($company->total_debt) }}</td>
                        <td><span class="badge {{ $company->is_active ? 'badge-success' : 'badge-danger' }}">{{ $company->is_active ? 'نشطة' : 'متوقفة' }}</span></td>
                        <td>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('installment-companies.panel', $company->id) }}" class="btn btn-outline-info btn-sm"><i class="fa-solid fa-table-cells-large text-xs"></i> لوحة التحكم</a>
                                <a href="{{ route('installment-companies.edit', $company->id) }}" class="btn btn-secondary btn-sm">تعديل</a>
                                <form method="POST" action="{{ route('installment-companies.destroy', $company->id) }}" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><x-empty-state text="لا توجد شركات" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection