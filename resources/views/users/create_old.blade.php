{{-- resources/views/users/create.blade.php --}}
@extends('layouts.app')

@section('title', 'إضافة موظف جديد')

@section('content')

    <x-page-header title="إضافة موظف جديد" subtitle="إنشاء حساب موظف وتحديد رتبته" icon="fa-user-plus">
        <x-slot:actions>
            <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-right text-xs"></i> رجوع</a>
        </x-slot:actions>
    </x-page-header>

    <form method="POST" action="{{ route('users.store') }}" class="max-w-4xl space-y-4">
        @csrf
        @include('users._form', ['user' => null])
    </form>

@endsection