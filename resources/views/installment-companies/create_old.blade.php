@extends('layouts.app')
@section('title', 'إضافة شركة تقسيط')
@section('content')
    <x-page-header title="إضافة شركة تقسيط" icon="fa-building" />
    <form method="POST" action="{{ route('installment-companies.store') }}" class="card max-w-xl space-y-4">
        @csrf
        @include('installment-companies._form')
    </form>
@endsection