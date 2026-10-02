{{-- <x-select-field name="role_id" label="الرتبة" :options="[1 => 'Owner']" :value="$user->role_id" placeholder="اختر..." /> --}}
@props(['name', 'label', 'options' => [], 'value' => null, 'placeholder' => null])

@php $current = old($name, $value); @endphp

<div>
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>

    <select id="{{ $name }}" name="{{ $name }}" {{ $attributes->merge(['class' => 'form-input']) }}>
        @if($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach($options as $key => $text)
            <option value="{{ $key }}" @selected((string) $current === (string) $key)>{{ $text }}</option>
        @endforeach
    </select>

    @error($name) <p class="form-error">{{ $message }}</p> @enderror
</div>