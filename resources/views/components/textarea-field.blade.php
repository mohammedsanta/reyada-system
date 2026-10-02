{{-- <x-textarea-field name="notes" label="ملاحظات" :value="$x->notes" rows="3" /> --}}
@props(['name', 'label', 'value' => null, 'rows' => 3])

<div>
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>

    <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" {{ $attributes->merge(['class' => 'form-input']) }}>{{ old($name, $value) }}</textarea>

    @error($name) <p class="form-error">{{ $message }}</p> @enderror
</div>