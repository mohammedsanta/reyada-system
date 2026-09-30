{{-- resources/views/users/index.blade.php --}}
@extends('layouts.app')

@section('title', 'إدارة المستخدمين')

@php
    // icon key => [font-awesome icon, color, tooltip]. Change the look of all rows here.
    $actionMap = [
        'profile'     => ['fa-user',         'cyan',    'عرض الحساب'],
        'team'        => ['fa-users',        'info',    'فريق العمل'],
        'banks'       => ['fa-building',     'warning', 'البنوك والشركات'],
        'permissions' => ['fa-shield-halved','accent',  'الصلاحيات'],
        'edit'        => ['fa-user-pen',     'orange',  'تعديل'],
    ];
@endphp

@section('content')

    {{-- HEADER --}}
    <x-page-header title="إدارة المستخدمين" subtitle="إدارة الموظفين والرتب والصلاحيات" icon="fa-users">
        <x-slot:actions>
            {{-- TODO: replace "#" with real routes when those pages exist --}}
            <a href="#" class="btn btn-outline-success btn-sm">
                <i class="fa-solid fa-file-excel"></i> إكسل
            </a>
            <a href="#" class="btn btn-outline-danger btn-sm">
                <i class="fa-solid fa-file-pdf"></i> PDF
            </a>
            <a href="#" class="btn btn-outline-info btn-sm">
                <i class="fa-solid fa-tag"></i> إضافة رتبة نظامية
            </a>
            <a href="#" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-user-plus"></i> إضافة موظف جديد
            </a>
        </x-slot:actions>
    </x-page-header>


    {{-- FILTERS (GET form: the URL keeps the filters) --}}
    <form method="GET" action="{{ route('users.index') }}" class="card filter-bar">

        {{-- Search --}}
        <div class="min-w-[240px] flex-1">
            <label for="search" class="sr-only">بحث</label>
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-dim"></i>
                <input
                    id="search" name="search" type="search"
                    value="{{ $filters['search'] }}"
                    placeholder="ابحث بالاسم، البريد، الكود أو الهاتف..."
                    class="form-input pl-9"
                >
            </div>
        </div>

        {{-- Role --}}
        <div class="w-48">
            <label for="role" class="form-label"><i class="fa-solid fa-filter ml-1 text-info"></i> الرتبة النظامية</label>
            <select id="role" name="role" class="form-input" onchange="this.form.submit()">
                <option value="">كل الصلاحيات</option>
                @foreach($roles as $role)
                    <option value="{{ $role }}" @selected($filters['role'] === $role)>{{ $role }}</option>
                @endforeach
            </select>
        </div>

        {{-- Status --}}
        <div class="w-44">
            <label for="status" class="form-label"><i class="fa-solid fa-circle-dot ml-1 text-info"></i> الحالة</label>
            <select id="status" name="status" class="form-input" onchange="this.form.submit()">
                <option value="">كل الحالات</option>
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        {{-- Reset --}}
        <a href="{{ route('users.index') }}" class="btn btn-secondary" title="إعادة ضبط">
            <i class="fa-solid fa-rotate-right"></i>
        </a>
    </form>


    {{-- TABLE --}}
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>كود المستخدم</th>
                    <th>الاسم الكامل</th>
                    <th>الهاتف</th>
                    <th>البريد الإلكتروني</th>
                    <th>الرتبة النظامية</th>
                    <th>الحالة</th>
                    <th>المشرف</th>
                    <th>البنوك والشركات</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>

            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td class="font-semibold text-fg">{{ $user->code }}</td>

                        <td class="font-semibold text-fg">
                            @if($user->is_system)
                                <i class="fa-solid fa-circle-check ml-1 text-[10px] text-info"></i>
                            @endif
                            {{ $user->name }}
                        </td>

                        <td><span dir="ltr">{{ $user->phone }}</span></td>
                        <td><span dir="ltr">{{ $user->email }}</span></td>

                        {{-- Role --}}
                        <td>
                            @if($user->role === 'Owner')
                                <span class="badge badge-owner gap-1.5 uppercase">
                                    Owner <i class="fa-solid fa-star text-[9px]"></i>
                                </span>
                            @else
                                <span class="badge badge-outline-info">{{ $user->role }}</span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td>
                            <span class="badge {{ $user->status === 'active' ? 'badge-success' : 'badge-danger' }}">
                                {{ $statuses[$user->status] ?? $user->status }}
                            </span>
                        </td>

                        {{-- Supervisor --}}
                        <td>
                            @if($user->supervisor)
                                @if($user->supervisor['is_owner'])
                                    <span class="badge badge-outline-info">({{ 'Owner' }}) {{ $user->supervisor['name'] }}</span>
                                @else
                                    <span class="tag">
                                        {{ $user->supervisor['name'] }}
                                        <span class="avatar-xs">{{ mb_substr($user->supervisor['name'], 0, 1) }}</span>
                                    </span>
                                @endif
                            @else
                                <span class="text-dim">-</span>
                            @endif
                        </td>

                        {{-- Banks --}}
                        <td>
                            <div class="flex flex-wrap gap-1">
                                @forelse($user->banks as $bank)
                                    <span class="tag">
                                        {{ $bank }}
                                        <i class="fa-solid fa-building-columns text-[10px] text-muted"></i>
                                    </span>
                                @empty
                                    <span class="text-dim">-</span>
                                @endforelse
                            </div>
                        </td>

                        {{-- Actions --}}
                        <td>
                            @if($user->is_system)
                                <span class="text-xs text-dim">حساب نظام</span>
                            @else
                                <div class="flex items-center gap-1.5">
                                    @foreach($user->actions as $key)
                                        @php [$icon, $color, $title] = $actionMap[$key]; @endphp
                                        <x-action-icon :icon="$icon" :color="$color" :title="$title" />
                                    @endforeach
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="py-10 text-center text-dim">لا يوجد مستخدمون مطابقون للبحث</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection