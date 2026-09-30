{{-- resources/views/components/client-field.blade.php
     Value is filled by JavaScript (data-field) when the modal opens. --}}
@props([
    'name',
    'label',
    'type'     => 'text',
    'options'  => null,     // array => renders a <select>
    'readonly' => false,
    'ltr'      => false,
    'step'     => null,
])

<div {{ $attributes->only('class') }}>
    <label for="client-{{ $name }}" class="form-label">{{ $label }}</label>

    @if($options)
        <select id="client-{{ $name }}" name="{{ $name }}" data-field="{{ $name }}" class="form-input">
            @foreach($options as $option)
                <option value="{{ $option }}">{{ $option }}</option>
            @endforeach
        </select>
    @else
        <input
            id="client-{{ $name }}" name="{{ $name }}" type="{{ $type }}"
            data-field="{{ $name }}"
            @if($step) step="{{ $step }}" @endif
            @if($ltr) dir="ltr" @endif
            @readonly($readonly)
            class="form-input read-only:opacity-60"
        >
    @endif

    @error($name)
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>