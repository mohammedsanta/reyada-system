{{-- resources/views/users/show.blade.php : employee profile --}}
@extends('layouts.app')

@section('title', 'ملف الموظف - ' . $user->name)

@section('content')

    <x-page-header title="ملف الموظف" :subtitle="$user->name . ' · ' . $user->code" icon="fa-user">
        <x-slot:actions>
            @unless($user->is_system)
                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-user-pen text-xs"></i> تعديل</a>
                <a href="{{ route('users.permissions.edit', $user->id) }}" class="btn btn-outline-info btn-sm"><i class="fa-solid fa-shield-halved text-xs"></i> الصلاحيات</a>
                <a href="{{ route('users.assignments.edit', $user->id) }}" class="btn btn-outline-info btn-sm"><i class="fa-solid fa-building text-xs"></i> البنوك والشركات</a>
                @if($user->role_id === 2)
                    <a href="{{ route('users.team', $user->id) }}" class="btn btn-outline-info btn-sm"><i class="fa-solid fa-users text-xs"></i> الفريق</a>
                @endif
            @endunless
            <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-right text-xs"></i> رجوع</a>
        </x-slot:actions>
    </x-page-header>

    {{-- Identity card --}}
    <div class="card mb-6 flex flex-wrap items-center gap-5">
        <x-avatar :name="$user->name" color="brand" size="h-16 w-16 text-base" />

        <div class="flex-1">
            <h2 class="text-xl font-bold text-fg">{{ $user->name }}</h2>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                @if($user->role === 'Owner')
                    <span class="badge badge-owner gap-1.5 uppercase">Owner <i class="fa-solid fa-star text-[9px]"></i></span>
                @else
                    <span class="badge badge-outline-info">{{ $user->role }}</span>
                @endif
                <x-status-badge type="user" :status="$user->status" />
                <span class="badge badge-neutral font-mono">{{ $user->code }}</span>
            </div>
        </div>

        <div class="flex items-center gap-8 text-center">
            <div><div class="text-[11px] text-dim">الحالات</div><div class="mt-1 text-xl font-bold text-fg">{{ $numbers['cases'] }}</div></div>
            <div><div class="text-[11px] text-dim">وعود محققة</div><div class="mt-1 text-xl font-bold text-fg">{{ $numbers['kept'] }}</div></div>
            <div><div class="text-[11px] text-dim">التحصيل (EGP)</div><div class="mt-1 text-xl font-bold text-brand">{{ number_format($numbers['collected']) }}</div></div>
        </div>
    </div>

    <section class="grid grid-cols-1 gap-4 xl:grid-cols-3">

        <div class="space-y-4 xl:col-span-2">

            {{-- Contact --}}
            <x-section-card title="بيانات الاتصال" icon="fa-address-card" color="info">
                <dl class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
                    <div><dt class="text-[11px] text-dim">البريد الإلكتروني</dt><dd class="mt-1 text-fg" dir="ltr">{{ $user->email }}</dd></div>
                    <div><dt class="text-[11px] text-dim">الهاتف</dt><dd class="mt-1 text-fg" dir="ltr">{{ $user->phone }}</dd></div>
                    <div>
                        <dt class="text-[11px] text-dim">المشرف</dt>
                        <dd class="mt-1 text-fg">{{ $user->supervisor['name'] ?? 'بدون مشرف' }}</dd>
                    </div>
                    <div><dt class="text-[11px] text-dim">آخر دخول</dt><dd class="mt-1 text-fg">{{ $user->last_login }}</dd></div>
                </dl>
            </x-section-card>

            {{-- Work scope --}}
            <x-section-card title="نطاق العمل" icon="fa-building" color="warning">
                <p class="mb-2 text-[11px] text-dim">البنوك</p>
                <div class="mb-4 flex flex-wrap gap-1.5">
                    @forelse($user->banks as $bank)
                        <span class="tag">{{ $bank }}<i class="fa-solid fa-building-columns text-[10px] text-muted"></i></span>
                    @empty
                        <span class="text-xs text-dim">{{ $user->is_system ? 'كل البنوك' : 'لا يوجد' }}</span>
                    @endforelse
                </div>

                <p class="mb-2 text-[11px] text-dim">شركات التقسيط</p>
                <div class="flex flex-wrap gap-1.5">
                    @forelse($companies as $company)
                        <span class="tag">{{ $company }}<i class="fa-solid fa-building text-[10px] text-muted"></i></span>
                    @empty
                        <span class="text-xs text-dim">{{ $user->is_system ? 'كل الشركات' : 'لا يوجد' }}</span>
                    @endforelse
                </div>
            </x-section-card>

            {{-- Permissions summary --}}
            <x-section-card title="الصلاحيات" icon="fa-shield-halved" color="accent">
                <p class="mb-4 text-xs text-muted">
                    <b class="text-fg">{{ $allowedCount }}</b> من {{ $totalCount }} صلاحية (رتبة {{ $role->label }}@if(count($user->overrides)) + استثناءات فردية@endif)
                </p>

                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    @foreach($modules as $module)
                        @php $percent = (int) round($module->allowed / $module->total * 100); @endphp
                        <div>
                            <div class="mb-1 flex justify-between text-[11px]">
                                <span class="text-muted">{{ $module->label }}</span>
                                <span class="text-fg">{{ $module->allowed }} / {{ $module->total }}</span>
                            </div>
                            <div class="progress"><div class="h-full rounded-full {{ $percent === 100 ? 'bg-brand' : ($percent > 0 ? 'bg-warning' : 'bg-white/10') }}" style="width: {{ $percent }}%"></div></div>
                        </div>
                    @endforeach
                </div>
            </x-section-card>
        </div>

        {{-- Recent activity --}}
        <x-section-card title="آخر النشاط" icon="fa-clock-rotate-left" color="cyan">
            <ol class="space-y-4">
                @foreach($activity as [$text, $time, $icon, $tone])
                    <li class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/[0.05] {{ $tone }}"><i class="fa-solid {{ $icon }} text-xs"></i></span>
                        <div>
                            <p class="text-sm text-fg">{{ $text }}</p>
                            <p class="mt-0.5 text-[11px] text-dim">{{ $time }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </x-section-card>

    </section>

@endsection