{{-- resources/views/banks/index.blade.php --}}
@extends('layouts.app')

@section('title', 'البنوك')

@section('content')

    <x-page-header title="البنوك" subtitle="إدارة جميع البنوك المسجلة">
        <x-slot:actions>
            <a href="{{ route('banks.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus text-xs"></i>
                إضافة بنك
            </a>
        </x-slot:actions>
    </x-page-header>

    <x-flash />

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الاسم</th>
                    <th>الكود</th>
                    <th>الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($banks as $bank)
                    <tr>
                        <td>{{ $bank->id }}</td>
                        <td class="font-semibold text-fg">{{ $bank->name }}</td>
                        <td>{{ $bank->code }}</td>
                        <td>
                            <span class="badge {{ $bank->is_active ? 'badge-success' : 'badge-danger' }}">
                                {{ $bank->is_active ? 'نشط' : 'متوقف' }}
                            </span>
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('banks.edit', $bank->id) }}" class="btn btn-secondary btn-sm">تعديل</a>

                                <form method="POST" action="{{ route('banks.destroy', $bank->id) }}"
                                      onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-dim">لا توجد بنوك</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection