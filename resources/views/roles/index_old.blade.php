{{-- resources/views/roles/index.blade.php --}}
@extends('layouts.app')

@section('title', 'الرتب النظامية')

@section('content')

    <x-page-header title="الرتب النظامية" subtitle="تحديد صلاحيات كل رتبة في النظام" icon="fa-user-shield">
        <x-slot:actions>
            <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm"><i class="fa-solid fa-users text-xs"></i> المستخدمون</a>
            <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus text-xs"></i> إضافة رتبة</a>
        </x-slot:actions>
    </x-page-header>

    <x-flash />

    @error('role')
        <div class="alert alert-danger mb-6">{{ $message }}</div>
    @enderror

    <div class="table-wrap">
        <table class="data-table whitespace-nowrap">
            <thead>
                <tr><th>الرتبة</th><th>الاسم البرمجي</th><th>الوصف</th><th>المستوى</th><th>الموظفون</th><th class="w-56">الصلاحيات</th><th>إجراءات</th></tr>
            </thead>

            <tbody>
                @foreach($roles as $role)
                    @php $percent = (int) round($role->permissions_count / $totalPermissions * 100); @endphp
                    <tr>
                        <td>
                            @if($role->is_system)
                                <span class="badge badge-owner gap-1.5 uppercase">{{ $role->label }} <i class="fa-solid fa-lock text-[9px]"></i></span>
                            @else
                                <span class="badge badge-outline-info">{{ $role->label }}</span>
                            @endif
                        </td>
                        <td><span class="font-mono text-xs" dir="ltr">{{ $role->name }}</span></td>
                        <td class="whitespace-normal min-w-[220px]">{{ $role->description }}</td>
                        <td>{{ $role->level }}</td>
                        <td>{{ $role->users_count }}</td>
                        <td>
                            <div class="mb-1 flex justify-between text-[11px] text-muted"><span>{{ $role->permissions_count }} / {{ $totalPermissions }}</span><span>{{ $percent }}%</span></div>
                            <div class="progress"><div class="h-full rounded-full bg-brand" style="width: {{ $percent }}%"></div></div>
                        </td>
                        <td>
                            @if($role->is_system)
                                <span class="text-xs text-dim">رتبة نظام</span>
                            @else
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-secondary btn-sm">تعديل</a>
                                    <form method="POST" action="{{ route('roles.destroy', $role->id) }}" onsubmit="return confirm('هل أنت متأكد من حذف الرتبة؟')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                                    </form>
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@endsection