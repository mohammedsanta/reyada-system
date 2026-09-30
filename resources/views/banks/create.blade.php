{{-- resources/views/banks/create.blade.php : example form page --}}
@extends('layouts.app')

@section('title', 'إضافة بنك')

@section('content')

    <x-page-header title="إضافة بنك" />

    <form method="POST" action="{{ route('banks.store') }}" class="card max-w-xl space-y-4">
        @csrf

        <x-form-field name="name" label="اسم البنك" placeholder="مثال: بنك مصر" />
        <x-form-field name="code" label="الكود" />

        <div class="flex gap-2 pt-2">
            <button type="submit" class="btn btn-primary">حفظ</button>
            <a href="{{ route('banks.index') }}" class="btn btn-secondary">إلغاء</a>
        </div>
    </form>

@endsection