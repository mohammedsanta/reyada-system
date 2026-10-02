{{-- resources/views/roles/create.blade.php --}}
@extends('layouts.app')

@section('title', 'إضافة رتبة نظامية')

@section('content')
    <x-page-header title="إضافة رتبة نظامية" subtitle="حدد اسم الرتبة وصلاحياتها" icon="fa-user-shield">
        <x-slot:actions>
            <a href="{{ route('roles.index') }}" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-right text-xs"></i> رجوع</a>
        </x-slot:actions>
    </x-page-header>

    <form method="POST" action="{{ route('roles.store') }}" class="max-w-6xl space-y-4">
        @csrf
        @include('roles._form', ['role' => null])
    </form>
@endsection