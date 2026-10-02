{{-- resources/views/users/permissions.blade.php --}}
@extends('layouts.app')

@section('title', 'صلاحيات الموظف - ' . $user->name)

@section('content')

    <x-page-header title="صلاحيات الموظف" :subtitle="$user->name . ' · رتبة ' . $role->label" icon="fa-shield-halved">
        <x-slot:actions>
            <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-right text-xs"></i> رجوع</a>
        </x-slot:actions>
    </x-page-header>

    <form id="permForm" method="POST" action="{{ route('users.permissions.update', $user->id) }}" class="space-y-4">
        @csrf
        @method('PUT')

        {{-- How it works + live summary --}}
        <div class="card flex flex-wrap items-center justify-between gap-4">
            <div class="max-w-xl">
                <p class="text-sm font-semibold text-fg">كل موظف يرث صلاحيات رتبته، ويمكنك استثناء صلاحيات فردية:</p>
                <p class="mt-1 text-[11px] leading-5 text-dim">
                    <b class="text-muted">افتراضي</b> = حسب الرتبة ·
                    <b class="text-brand">منح</b> = صلاحية إضافية ·
                    <b class="text-danger">سحب</b> = إزالة صلاحية تمنحها الرتبة
                </p>
            </div>

            <div class="flex items-center gap-6 text-center">
                <div><div class="text-[11px] text-dim">مسموح</div><div id="sum-allowed" class="text-xl font-bold text-brand">0</div></div>
                <div><div class="text-[11px] text-dim">منح إضافي</div><div id="sum-grants" class="text-xl font-bold text-fg">0</div></div>
                <div><div class="text-[11px] text-dim">سحب</div><div id="sum-revokes" class="text-xl font-bold text-danger">0</div></div>
            </div>
        </div>

        @error('override') <p class="form-error">{{ $message }}</p> @enderror

        {{-- Permission groups --}}
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            @foreach($groups as $key => $group)
                <x-section-card :title="$group->label" icon="fa-shield-halved" color="accent">
                    <div class="space-y-2">
                        @foreach($group->items as $permission)
                            <div class="perm-row flex flex-wrap items-center justify-between gap-3 rounded-xl border border-line bg-white/[0.02] px-3 py-2"
                                 data-role="{{ $permission->from_role ? 1 : 0 }}">
                                <div class="min-w-0">
                                    <div class="text-sm text-fg">{{ $permission->label }}</div>
                                    <div class="text-[10px] text-dim">
                                        <span dir="ltr">{{ $permission->name }}</span>
                                        @if($permission->from_role) · <span class="text-brand">ضمن الرتبة</span> @endif
                                    </div>
                                </div>

                                <div class="flex items-center gap-1.5">
                                    @foreach(['inherit' => 'افتراضي', 'grant' => 'منح', 'revoke' => 'سحب'] as $value => $label)
                                        <label>
                                            <input type="radio" name="override[{{ $permission->id }}]" value="{{ $value }}" class="pill-radio sr-only"
                                                   @checked(old("override.{$permission->id}", $permission->override) === $value)>
                                            <span class="pill">{{ $label }}</span>
                                        </label>
                                    @endforeach

                                    <span class="final-badge badge w-14 justify-center"></span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-section-card>
            @endforeach
        </div>

        <div class="flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk text-xs"></i> حفظ الصلاحيات</button>
            <button type="button" id="resetAll" class="btn btn-secondary"><i class="fa-solid fa-rotate-left text-xs"></i> إعادة الكل للافتراضي</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">إلغاء</a>
        </div>
    </form>

@endsection

@push('scripts')
    <script>
        const form = document.getElementById('permForm');

        // final = (role has it and not revoked) or granted
        function refresh() {
            let allowed = 0, grants = 0, revokes = 0;

            form.querySelectorAll('.perm-row').forEach(row => {
                const fromRole = row.dataset.role === '1';
                const choice   = row.querySelector('input[type="radio"]:checked').value;
                const final    = choice === 'grant' || (choice === 'inherit' && fromRole);
                const badge    = row.querySelector('.final-badge');

                badge.textContent = final ? 'مسموح' : 'ممنوع';
                badge.className   = 'final-badge badge w-14 justify-center ' + (final ? 'badge-success' : 'badge-danger');

                if (final) allowed++;
                if (choice === 'grant' && !fromRole) grants++;
                if (choice === 'revoke' && fromRole) revokes++;
            });

            document.getElementById('sum-allowed').textContent = allowed;
            document.getElementById('sum-grants').textContent  = grants;
            document.getElementById('sum-revokes').textContent = revokes;
        }

        form.addEventListener('change', refresh);

        document.getElementById('resetAll').addEventListener('click', () => {
            form.querySelectorAll('input[type="radio"][value="inherit"]').forEach(radio => (radio.checked = true));
            refresh();
        });

        refresh();
    </script>
@endpush