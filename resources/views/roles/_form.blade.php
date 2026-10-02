{{-- resources/views/roles/_form.blade.php : shared by create and edit. $role = null when creating. Needs $groups. --}}
@php
    $editing  = $role !== null;
    $selected = array_map('intval', old('permissions', $role?->permission_ids ?? []));
@endphp

<x-section-card title="بيانات الرتبة" icon="fa-tag" color="info">
    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
        <x-form-field name="label" label="اسم الرتبة (يظهر في النظام)" :value="$role?->label" placeholder="مثال: Team Leader" />
        <x-form-field name="name" label="الاسم البرمجي" :value="$role?->name" dir="ltr" placeholder="team_leader" />
        <x-form-field name="level" label="المستوى (1 - 99)" type="number" :value="$role?->level ?? 10" min="1" max="99" />
        <x-form-field name="description" label="الوصف" :value="$role?->description" placeholder="ماذا تفعل هذه الرتبة؟" />
    </div>
    <p class="mt-2 text-[11px] text-dim">المستوى الأعلى = سلطة أكبر. الاسم البرمجي بحروف إنجليزية صغيرة وشرطة سفلية فقط.</p>
</x-section-card>

<x-section-card title="الصلاحيات" icon="fa-shield-halved" color="accent">
    @error('permissions') <p class="form-error mb-3">{{ $message }}</p> @enderror

    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
        @foreach($groups as $key => $group)
            <div class="rounded-xl border border-line bg-white/[0.02] p-4">
                {{-- master checkbox: selects the whole module (handled in app.js: data-check-all) --}}
                <label class="mb-3 flex cursor-pointer items-center gap-2 text-sm font-bold text-fg">
                    <input type="checkbox" class="accent-brand" data-check-all=".perm-{{ $key }}">
                    {{ $group->label }}
                </label>

                <div class="grid grid-cols-2 gap-2">
                    @foreach($group->items as $permission)
                        <label class="check-row">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                   class="perm-{{ $key }} accent-brand" @checked(in_array($permission->id, $selected, true))>
                            {{ $permission->label }}
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</x-section-card>

<div class="flex gap-2">
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk text-xs"></i> حفظ الرتبة</button>
    <a href="{{ route('roles.index') }}" class="btn btn-secondary">إلغاء</a>
</div>