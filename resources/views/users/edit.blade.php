{{-- resources/views/users/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'تعديل الموظف - ' . $user->name)

@section('content')

    <x-page-header title="تعديل بيانات الموظف" :subtitle="$user->name . ' · ' . $user->code" icon="fa-user-pen">
        <x-slot:actions>
            <span class="text-[11px] text-dim">آخر دخول: {{ $user->last_login }}</span>
            <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-right text-xs"></i> رجوع</a>
        </x-slot:actions>
    </x-page-header>

    <x-flash />

    <form method="POST" action="{{ route('users.update', $user->id) }}" class="max-w-4xl space-y-4">
        @csrf
        @method('PUT')
        @include('users._form', ['user' => $user])
    </form>

    {{-- Delete (a separate form: forms cannot be nested) --}}
    <div class="card mt-6 flex max-w-4xl flex-wrap items-center justify-between gap-4 border-danger/20">
        <div>
            <h3 class="text-sm font-bold text-danger">حذف الموظف</h3>
            <p class="mt-1 text-xs text-muted">يتم إخفاء الحساب ولا يستطيع الدخول، وتبقى سجلات عمله محفوظة.</p>
        </div>

        <form method="POST" action="{{ route('users.destroy', $user->id) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذا الموظف؟')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger"><i class="fa-solid fa-trash text-xs"></i> حذف الموظف</button>
        </form>
    </div>

@endsection