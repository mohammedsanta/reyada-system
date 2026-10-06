@extends('layouts.app')

@section('title', 'أداء الموظفين')

@section('content')

    <x-page-header
        title="أداء الموظفين"
        subtitle="مركز القياس التحليلي الذكي"
        icon="fa-rocket"
    >
        <x-slot:actions>
            <a
                href="{{ request()->fullUrl() }}"
                class="btn btn-secondary btn-sm"
                id="refreshEmployees"
            >
                <i class="fa-solid fa-rotate"></i>
                تحديث فوري
            </a>
        </x-slot:actions>
    </x-page-header>

    @include('employees.partials.summary', [
        'summary' => $summary,
    ])

    @include('employees.partials.filters', [
        'filterState' => $filterState,
    ])

    @include('employees.partials.ranking', [
        'employees' => $employees,
        'summary' => $summary,
        'filterState' => $filterState,
    ])

@endsection

@include('employees.partials.scripts')