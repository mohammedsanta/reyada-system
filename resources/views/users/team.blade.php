{{-- resources/views/users/team.blade.php --}}
@extends('layouts.app')

@section('title', 'فريق العمل - ' . $supervisor->name)

@php
    $toneText = ['brand' => 'text-brand', 'warning' => 'text-warning', 'danger' => 'text-danger'];
    $toneBar  = ['brand' => 'bg-brand',   'warning' => 'bg-warning',   'danger' => 'bg-danger'];
    $profile  = fn ($member) => \Illuminate\Support\Facades\Route::has('employees.show') ? route('employees.show', $member->code) : '#';
@endphp

@section('content')

    <x-page-header title="فريق العمل" :subtitle="'تحت إشراف ' . $supervisor->name" icon="fa-users">
        <x-slot:actions>
            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-user-plus text-xs"></i> إضافة موظف</a>
            <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-right text-xs"></i> رجوع</a>
        </x-slot:actions>
    </x-page-header>

    {{-- Supervisor --}}
    <div class="card mb-6 flex flex-wrap items-center gap-4">
        <x-avatar :name="$supervisor->name" color="accent" size="h-12 w-12" />
        <div>
            <p class="text-sm font-bold text-fg">{{ $supervisor->name }}</p>
            <p class="mt-1 flex items-center gap-2 text-xs text-muted">
                <span class="badge badge-outline-info">{{ $supervisor->role }}</span>
                <span dir="ltr">{{ $supervisor->phone }}</span>
            </p>
        </div>
    </div>

    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($stats as $stat)
            <x-metric-card :label="$stat['label']" :value="$stat['value']" :unit="$stat['unit']" :icon="$stat['icon']" :color="$stat['color']" />
        @endforeach
    </section>

    {{-- Members --}}
    <div class="table-wrap">
        <table class="data-table whitespace-nowrap">
            <thead>
                <tr><th>الموظف</th><th>الرتبة</th><th>الحالة</th><th>الحالات</th><th>وعود محققة</th><th>التحصيل</th><th class="w-48">الكفاءة</th><th>آخر دخول</th><th></th></tr>
            </thead>

            <tbody>
                @forelse($members as $member)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <x-avatar :name="$member->name" />
                                <div>
                                    <div class="font-semibold text-fg">{{ $member->name }}</div>
                                    <div class="text-[11px] text-dim">{{ $member->code }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge badge-outline-info">{{ $member->role }}</span></td>
                        <td><x-status-badge type="user" :status="$member->status" /></td>
                        <td>{{ $member->cases }}</td>
                        <td>{{ $member->kept }}</td>
                        <td class="font-semibold text-brand">EGP {{ number_format($member->collected) }}</td>
                        <td>
                            <div class="mb-1 flex justify-between text-[11px]"><span class="text-muted">الكفاءة</span><span class="font-bold {{ $toneText[$member->tone] }}">{{ $member->efficiency }}%</span></div>
                            <div class="progress"><div class="h-full rounded-full {{ $toneBar[$member->tone] }}" style="width: {{ $member->efficiency }}%"></div></div>
                        </td>
                        <td>{{ $member->last_login }}</td>
                        <td><a href="{{ $profile($member) }}" class="text-dim transition hover:text-fg" title="تفاصيل الأداء"><i class="fa-solid fa-chevron-left"></i></a></td>
                    </tr>
                @empty
                    <tr><td colspan="9"><x-empty-state icon="fa-users-slash" text="لا يوجد موظفون تحت إشراف هذا الموظف" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection