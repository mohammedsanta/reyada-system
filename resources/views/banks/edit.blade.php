{{-- resources/views/banks/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'تعديل بنك')

@section('content')

    <x-page-header title="تعديل بنك" :subtitle="$bank->name" />

    <form method="POST" action="{{ route('banks.update', $bank->id) }}" class="card max-w-xl space-y-4">
        @csrf
        @method('PUT')
        @include('banks._form', ['bank' => $bank])
    </form>

@endsection