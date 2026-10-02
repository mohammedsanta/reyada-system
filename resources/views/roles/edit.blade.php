{{-- resources/views/roles/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'تعديل رتبة - ' . $role->label)

@section('content')
    <x-page-header title="تعديل رتبة نظامية" :subtitle="$role->label . ' · ' . $role->users_count . ' موظفين'" icon="fa-user-shield">
        <x-slot:actions>
            <a href="{{ route('roles.index') }}" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-right text-xs"></i> رجوع</a>
        </x-slot:actions>
    </x-page-header>

    <form method="POST" action="{{ route('roles.update', $role->id) }}" class="max-w-6xl space-y-4">
        @csrf
        @method('PUT')
        @include('roles._form', ['role' => $role])
    </form>
@endsection