@extends('layouts.app')
@section('title', 'تعديل شركة تقسيط')
@section('content')
    <x-page-header title="تعديل شركة تقسيط" :subtitle="$company->name" icon="fa-building" />
    <form method="POST" action="{{ route('installment-companies.update', $company->id) }}" class="card max-w-xl space-y-4">
        @csrf @method('PUT')
        @include('installment-companies._form', ['company' => $company])
    </form>
@endsection