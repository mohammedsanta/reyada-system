{{-- resources/views/employees/show.blade.php --}}

@extends('layouts.app')

@section('title', 'أداء الموظف - ' . $report['employee']['name'])

@section('content')

    {{-- ================================================================
         PAGE HEADER
         ================================================================ --}}

    <x-page-header
        title="تفاصيل أداء الموظف"
        :subtitle="$report['employee']['name'] . ' · ' . $report['employee']['code']"
        icon="fa-chart-line"
    >
        <x-slot:actions>

            <button
                type="button"
                onclick="window.print()"
                class="btn btn-secondary btn-sm"
                title="طباعة التقرير"
            >
                <i class="fa-solid fa-print text-xs"></i>
                طباعة
            </button>

            <a
                href="{{ route('users.show', $user->id) }}"
                class="btn btn-outline-info btn-sm"
            >
                <i class="fa-solid fa-user text-xs"></i>
                ملف الموظف
            </a>

            <a
                href="{{ route('employees.index') }}"
                class="btn btn-secondary btn-sm"
            >
                <i class="fa-solid fa-arrow-right text-xs"></i>
                رجوع
            </a>

        </x-slot:actions>
    </x-page-header>


    {{-- Employee identity + filters --}}
    @include('employees.show._identity')


    {{-- Performance overview --}}
    @include('employees.show._overview')


    {{-- Additional metrics --}}
    @include('employees.show._additional-metrics')


    {{-- Performance insights --}}
    @include('employees.show._insights')


    {{-- Charts --}}
    @include('employees.show._charts')


    {{-- Bank + recent promises --}}
    <section class="grid grid-cols-1 gap-4 xl:grid-cols-2">

        @include('employees.show._bank-performance')

        @include('employees.show._recent-promises')

    </section>


    {{-- Footer summary --}}
    @include('employees.show._footer-summary')


    {{-- Report footer --}}
    @include('employees.show._report-footer')

@endsection


@push('scripts')

    @include('employees.show._scripts')

@endpush