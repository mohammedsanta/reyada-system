{{-- resources/views/users/_form.blade.php : shared by create and edit.
     $user = null when creating. Needs: $roles, $supervisors, $statuses (and $nextCode on create). --}}
@php $editing = $user !== null; @endphp

<x-section-card title="البيانات الأساسية" icon="fa-id-card" color="info">
    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
        <x-form-field name="name" label="الاسم الكامل" :value="$user?->name" placeholder="مثال: أحمد محمد" />
        <x-form-field name="code" label="كود الموظف" :value="$user?->code ?? ($nextCode ?? '')" readonly class="form-input opacity-60" />
        <x-form-field name="email" label="البريد الإلكتروني" type="email" :value="$user?->email" dir="ltr" placeholder="name@collex.com" />
        <x-form-field name="phone" label="رقم الهاتف" :value="$user?->phone" dir="ltr" placeholder="01XXXXXXXXX" />
    </div>
    @unless($editing)
        <p class="mt-2 text-[11px] text-dim">كود الموظف يتم توليده تلقائياً عند الحفظ.</p>
    @endunless
</x-section-card>

<x-section-card title="الرتبة والإشراف" icon="fa-user-shield" color="accent">
    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
        <x-select-field name="role_id" label="الرتبة النظامية" :options="$roles" :value="$user?->role_id" placeholder="اختر الرتبة..." />
        <x-select-field name="supervisor_id" label="المشرف" :options="$supervisors" :value="$user?->supervisor_id" placeholder="بدون مشرف" />
        <x-select-field name="status" label="الحالة" :options="$statuses" :value="$user?->status ?? 'active'" />
    </div>
</x-section-card>

<x-section-card title="كلمة المرور" icon="fa-lock" color="warning">
    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
        <x-form-field name="password" label="كلمة المرور" type="password" autocomplete="new-password" />
        <x-form-field name="password_confirmation" label="تأكيد كلمة المرور" type="password" autocomplete="new-password" />
    </div>
    @if($editing)
        <p class="mt-2 text-[11px] text-dim">اتركها فارغة إذا لا تريد تغيير كلمة المرور.</p>
    @else
        <p class="mt-2 text-[11px] text-dim">8 أحرف على الأقل.</p>
    @endif
</x-section-card>

<div class="flex gap-2">
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk text-xs"></i> حفظ</button>
    <a href="{{ route('users.index') }}" class="btn btn-secondary">إلغاء</a>
</div>