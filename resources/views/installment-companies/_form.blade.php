{{-- shared by create and edit --}}
@php $company = $company ?? null; @endphp

<x-form-field name="name" label="اسم الشركة" :value="$company?->name" />
<x-form-field name="code" label="الكود" :value="$company?->code" />

<label class="check-row">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" class="accent-brand" @checked(old('is_active', $company?->is_active ?? true))>
    الشركة نشطة
</label>

<div class="flex gap-2 pt-2">
    <button type="submit" class="btn btn-primary">حفظ</button>
    <a href="{{ route('installment-companies.index') }}" class="btn btn-secondary">إلغاء</a>
</div>