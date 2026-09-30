{{-- resources/views/components/form-field.blade.php --}}
@props(['name', 'label', 'type' => 'text', 'value' => null])

<div>
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        {{ $attributes->merge(['class' => 'form-input']) }}
    >

    @error($name)
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>