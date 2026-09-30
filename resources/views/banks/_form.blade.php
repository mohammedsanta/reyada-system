{{-- resources/views/banks/_form.blade.php : shared by create and edit --}}
@props(['bank' => null])

<x-form-field name="name" label="اسم البنك" :value="$bank?->name" placeholder="مثال: بنك مصر" />
<x-form-field name="code" label="الكود" :value="$bank?->code" placeholder="مثال: BM" />

<div class="flex gap-2 pt-2">
    <button type="submit" class="btn btn-primary">حفظ</button>
    <a href="{{ route('banks.index') }}" class="btn btn-secondary">إلغاء</a>
</div>