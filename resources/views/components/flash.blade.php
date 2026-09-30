{{-- resources/views/components/flash.blade.php --}}
@if(session('success'))
    <div class="alert alert-success mb-6">{{ session('success') }}</div>
@endif