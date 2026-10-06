{{-- resources/views/banks/index.blade.php --}}
@extends('layouts.app')

@section('title', 'البنوك')

@section('content')

    <x-page-header title="البنوك" subtitle="اختر بنكاً لفتح لوحة تحكم النطاق الخاصة به" icon="fa-building-columns">
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
                    <th>البنك</th>
                    <th>الكود</th>
                    <th>الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>

            <tbody>
                @forelse($banks as $bank)
                    <tr>
                        <td>{{ $bank->id }}</td>

                        {{-- Bank name => opens /banks/{id}/panel --}}
                        <td>
                            <a href="{{ route('banks.panel', $bank->id) }}" class="group inline-flex items-center gap-3">
                                <span class="flex h-9 w-9 items-center justify-center overflow-hidden rounded-lg bg-white text-black">
                                    @if($bank->logo ?? null)
                                        <img src="{{ $bank->logo }}" alt="{{ $bank->name }}" class="h-full w-full object-contain p-1">
                                    @else
                                        <i class="fa-solid fa-building-columns text-sm"></i>
                                    @endif
                                </span>

                                <span class="font-semibold text-fg transition group-hover:text-brand">
                                    {{ $bank->name }}
                                </span>
                            </a>
                        </td>

                        <td>{{ $bank->code }}</td>

                        <td>
                            <span class="badge {{ $bank->is_active ? 'badge-success' : 'badge-danger' }}">
                                {{ $bank->is_active ? 'نشط' : 'متوقف' }}
                            </span>
                        </td>

                        <td>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('banks.panel', $bank->id) }}" class="btn btn-outline-info btn-sm">
                                    <i class="fa-solid fa-table-cells-large text-xs"></i> لوحة التحكم
                                </a>

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